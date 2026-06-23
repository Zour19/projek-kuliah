# 🚀 Deployment & Maintenance Guide

## Production Deployment

### Prerequisites
- Docker & Docker Compose
- SSL Certificate (for HTTPS)
- Domain name
- Server with at least 2GB RAM, 10GB storage
- Ubuntu 20.04+ or equivalent

### Step 1: Prepare Server

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify installation
docker --version
docker-compose --version
```

### Step 2: Setup Application

```bash
# Clone repository
git clone <repository-url> /var/www/florist-shop
cd /var/www/florist-shop

# Setup directory permissions
sudo chown -R $USER:$USER /var/www/florist-shop
```

### Step 3: Configure Environment

```bash
cd laravel_app

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Update .env for production
```

**Production .env configuration:**

```env
APP_NAME="Florist Shop"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

LOG_CHANNEL=single
LOG_LEVEL=error

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@florist-shop.com
MAIL_FROM_NAME="Florist Shop"

# Security
APP_KEY=your_generated_key
```

### Step 4: Setup SSL Certificate

```bash
# Using Let's Encrypt (free)
sudo apt install certbot python3-certbot-nginx -y

# Get certificate
sudo certbot certonly --standalone -d yourdomain.com -d www.yourdomain.com

# Certificate path will be at:
# /etc/letsencrypt/live/yourdomain.com/fullchain.pem
# /etc/letsencrypt/live/yourdomain.com/privkey.pem
```

### Step 5: Configure Nginx for Production

Create `docker/nginx/conf.d/app.prod.conf`:

```nginx
upstream php-fpm {
    server app:9000;
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

# HTTPS Server
server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    root /app/public;
    index index.php;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css text/xml text/javascript 
               application/x-javascript application/xml+rss 
               application/javascript application/json;

    # Static files caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass php-fpm;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /^(database|storage|vendor)/ {
        deny all;
    }

    # Routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### Step 6: Create Production Docker Compose

Create `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  app:
    build: .
    container_name: florist-app
    working_dir: /app
    restart: always
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    volumes:
      - ./laravel_app:/app
      - florist-logs:/app/storage/logs
      - florist-database:/app/database
    networks:
      - florist-network
    depends_on:
      - nginx

  nginx:
    image: nginx:alpine
    container_name: florist-nginx
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./laravel_app/public:/app/public
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf:ro
      - ./docker/nginx/conf.d/app.prod.conf:/etc/nginx/conf.d/app.conf:ro
      - /etc/letsencrypt:/etc/letsencrypt:ro
    networks:
      - florist-network
    depends_on:
      - app

volumes:
  florist-logs:
  florist-database:

networks:
  florist-network:
    driver: bridge
```

### Step 7: Deploy Application

```bash
# Navigate to project
cd /var/www/florist-shop

# Build and start containers
docker-compose -f docker-compose.prod.yml up -d

# Run migrations
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Seed data
docker-compose -f docker-compose.prod.yml exec app php artisan db:seed --force

# Build frontend assets
docker-compose -f docker-compose.prod.yml exec app npm run build

# Clear caches
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache
```

### Step 8: Setup Monitoring

```bash
# Install monitoring tools
docker-compose -f docker-compose.prod.yml exec app php artisan tinker

# Check disk space
df -h

# Monitor logs
docker-compose -f docker-compose.prod.yml logs -f app

# Check container status
docker-compose -f docker-compose.prod.yml ps
```

### Step 9: Setup Automated Backups

Create backup script `backup.sh`:

```bash
#!/bin/bash

BACKUP_DIR="/backups"
PROJECT_DIR="/var/www/florist-shop"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
sqlite3 $PROJECT_DIR/laravel_app/database/database.sqlite ".backup $BACKUP_DIR/florist_db_$DATE.db"

# Backup storage
tar -czf $BACKUP_DIR/florist_storage_$DATE.tar.gz $PROJECT_DIR/laravel_app/storage

# Keep only last 7 days of backups
find $BACKUP_DIR -mtime +7 -delete

# Log backup completion
echo "Backup completed: $DATE" >> $BACKUP_DIR/backup.log
```

Setup cron job:
```bash
# Edit crontab
sudo crontab -e

# Add backup job (daily at 2 AM)
0 2 * * * /var/www/florist-shop/backup.sh
```

## Maintenance

### Daily Maintenance

```bash
# Monitor logs
docker-compose -f docker-compose.prod.yml logs --tail=100

# Check disk usage
df -h

# Verify application health
curl -I https://yourdomain.com
```

### Weekly Maintenance

```bash
# Clear old logs
docker-compose -f docker-compose.prod.yml exec app php artisan logs:clear

# Database optimization (SQLite)
docker-compose -f docker-compose.prod.yml exec app php artisan tinker
# Run: DB::statement('VACUUM;')
```

### Monthly Maintenance

```bash
# Update dependencies
cd laravel_app
composer update --no-dev
npm update

# Rebuild containers
docker-compose -f docker-compose.prod.yml build --no-cache

# Restart services
docker-compose -f docker-compose.prod.yml restart
```

### Emergency Procedures

#### Application Crash

```bash
# Check logs
docker-compose -f docker-compose.prod.yml logs app

# Restart containers
docker-compose -f docker-compose.prod.yml restart

# If still failing, rebuild
docker-compose -f docker-compose.prod.yml build --no-cache
docker-compose -f docker-compose.prod.yml up -d
```

#### Restore from Backup

```bash
# Stop containers
docker-compose -f docker-compose.prod.yml stop

# Restore database
cp /backups/florist_db_20240115_020000.db laravel_app/database/database.sqlite

# Restore storage
tar -xzf /backups/florist_storage_20240115_020000.tar.gz

# Start containers
docker-compose -f docker-compose.prod.yml up -d
```

#### Database Corruption

```bash
# Repair SQLite database
docker-compose -f docker-compose.prod.yml exec app php artisan tinker
# Run: DB::statement('PRAGMA integrity_check;')

# If corrupted, restore from backup
cp /backups/florist_db_latest.db laravel_app/database/database.sqlite
```

## Scaling Strategies

### Vertical Scaling
- Increase server RAM/CPU
- Optimize database queries
- Enable query caching

### Horizontal Scaling (Future)
- Use load balancer (Nginx, HAProxy)
- Deploy multiple app containers
- Use external database (MySQL/PostgreSQL)
- Implement Redis for caching/sessions

```yaml
# Future: Load Balancer Configuration
  lb:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/load-balancer.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - app1
      - app2
      - app3
```

## Performance Optimization

### Database
```bash
# Add indexes
docker-compose -f docker-compose.prod.yml exec app php artisan tinker
# DB::statement('CREATE INDEX idx_products_category ON products(category_id);')
# DB::statement('CREATE INDEX idx_products_slug ON products(slug);')
# DB::statement('CREATE INDEX idx_orders_email ON orders(customer_email);')
```

### Caching
```bash
# Enable caching
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache
```

### Asset Optimization
```bash
# Build assets for production
docker-compose -f docker-compose.prod.yml exec app npm run build

# Verify gzip compression
curl -I -H "Accept-Encoding: gzip" https://yourdomain.com
```

## Security Checklist

- ✅ SSL/TLS enabled
- ✅ Security headers configured
- ✅ APP_DEBUG set to false
- ✅ .env file not committed
- ✅ SSH keys configured
- ✅ Firewall rules configured
- ✅ Regular backups enabled
- ✅ Log monitoring enabled
- ✅ Database password strong
- ✅ API rate limiting configured

## Monitoring & Alerts

Setup alert system (future integration):
- Email notifications on deployment
- Slack integration for errors
- Uptime monitoring (Uptime Robot)
- Performance monitoring (New Relic, Datadog)
- Log aggregation (ELK Stack)

## Troubleshooting

### Container won't start
```bash
docker-compose logs app
# Check: APP_KEY, permissions, disk space
```

### High memory usage
```bash
docker stats
# Check: queued jobs, memory leaks, cache size
```

### Slow queries
```bash
docker-compose exec app php artisan tinker
# Enable query logging: DB::enableQueryLog();
```

### SSL certificate renewal
```bash
sudo certbot renew
docker-compose -f docker-compose.prod.yml restart nginx
```

---

**For questions or issues, refer to the main README.md or create a GitHub issue.**
