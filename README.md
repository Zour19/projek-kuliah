# 🌸 Florist Shop - Professional E-commerce Platform

A modern, production-ready flower shop e-commerce platform built with **Laravel 13**, **Vue.js**, and **Docker**. Features comprehensive RESTful API, responsive design, and professional-grade infrastructure.

## 📋 Features

### Core Features
- ✅ Modern responsive e-commerce interface with Tailwind CSS
- ✅ Product catalog with 5 categories (Bouquets, Standing Flowers, Bloom Box, Flowers, Accessories)
- ✅ Shopping cart with real-time calculation
- ✅ Order management system with 6-step workflow (pending → confirmed → processing → shipped → delivered)
- ✅ Customer order tracking by email and order number
- ✅ Professional admin panel for order management
- ✅ RESTful API v1 with full CRUD operations
- ✅ Advanced product filtering and search

### Technical Features
- ✅ **Laravel 13** - Modern PHP framework with Eloquent ORM
- ✅ **Docker & Docker Compose** - Development and production containerization
- ✅ **SQLite Database** - Lightweight, file-based SQL database
- ✅ **Blade Templating** - Server-side templating with components
- ✅ **Vite** - Modern asset bundling
- ✅ **GitHub Actions** - Automated CI/CD pipeline
- ✅ **Multi-language Ready** - Indonesian/English support structure
- ✅ **Security Headers** - CORS, CSRF, XSS protection

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose (recommended)
- OR: PHP 8.3+, Node.js 18+, Composer

### Option 1: Docker (Recommended)

```bash
# Clone and setup
git clone <repository-url>
cd "projek akhir semester"

# Start all services
docker-compose up -d

# Setup database
docker-compose exec app php artisan migrate --seed

# Build frontend assets
docker-compose exec app npm run build

# Access application
# Web: http://localhost
# API: http://localhost/api/v1
```

### Option 2: Local Development

```bash
# Navigate to Laravel app
cd laravel_app

# Install PHP dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create and seed database
touch database/database.sqlite
php artisan migrate --seed

# Install and build frontend
npm install
npm run dev

# Start development server
php artisan serve --host=0.0.0.0 --port=8000
```

Visit: [http://localhost:8000](http://localhost:8000)

## 📁 Project Structure

```
.
├── laravel_app/                    # Main Laravel application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── ShopController.php       # Web interface
│   │   │   │   └── Api/                     # API controllers
│   │   │   │       ├── ProductController.php
│   │   │   │       ├── CategoryController.php
│   │   │   │       └── OrderController.php
│   │   │   └── Requests/
│   │   │       └── StoreOrderRequest.php    # Request validation
│   │   └── Models/
│   │       ├── Category.php
│   │       ├── Product.php
│   │       ├── Order.php
│   │       └── OrderItem.php
│   ├── database/
│   │   ├── migrations/                      # Database schema
│   │   ├── factories/                       # Test data generators
│   │   └── seeders/
│   │       └── DatabaseSeeder.php           # Initial data
│   ├── resources/
│   │   ├── views/
│   │   │   ├── home.blade.php
│   │   │   ├── category.blade.php
│   │   │   └── components/
│   │   │       └── layouts/app.blade.php
│   │   └── css/
│   ├── routes/
│   │   ├── web.php
│   │   └── api.php                          # API routes
│   ├── docker/
│   │   └── nginx/
│   │       ├── nginx.conf
│   │       └── conf.d/app.conf
│   ├── docker-compose.yml
│   └── Dockerfile
├── assets/                                   # Legacy florist assets
│   └── images/
│       ├── bouquets/
│       ├── standing-flowers/
│       ├── bloom-box/
│       ├── flowers/
│       ├── accessories/
│       └── unsorted/
└── README.md
```

## 🔌 RESTful API Documentation

### Base URL: `/api/v1`

### Products Endpoints

**List Products**
```http
GET /api/v1/products
```
Query Parameters:
- `category` - Filter by category slug
- `featured` - true/false (boolean)
- `status` - active/inactive
- `search` - Search term
- `sort` - Sort field (default: created_at)
- `order` - asc/desc (default: desc)
- `per_page` - 1-100 (default: 12)

Response:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Romantic Rose Bouquet",
      "slug": "romantic-rose-bouquet",
      "price": "250000.00",
      "image": "assets/images/bouquets/bouqets1.jpeg",
      "is_featured": true,
      "status": "active",
      "stock": 50
    }
  ],
  "pagination": {...}
}
```

**Get Featured Products**
```http
GET /api/v1/products/featured
```

**Get Single Product**
```http
GET /api/v1/products/{id}
```

### Categories Endpoints

**List Categories**
```http
GET /api/v1/categories
```

Response:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Bouquets",
      "slug": "bouquets",
      "description": "Hand-tied bouquets for every occasion.",
      "image": "assets/images/bouquets/bouqet.jpeg"
    }
  ]
}
```

**Get Category with Products**
```http
GET /api/v1/categories/{slug}
```

### Orders Endpoints

**Create Order**
```http
POST /api/v1/orders
Content-Type: application/json
```

Request Body:
```json
{
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "+62812345678",
  "delivery_address": "Jl. Merdeka No. 1, Jakarta",
  "notes": "Please wrap nicely",
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 3,
      "quantity": 1
    }
  ]
}
```

Response (201):
```json
{
  "message": "Order berhasil dibuat",
  "data": {
    "id": 1,
    "order_number": "ORD-20240115-ABC123",
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "total_price": "650000.00",
    "status": "pending",
    "items": [...]
  }
}
```

**Get Order Status**
```http
GET /api/v1/orders/{order_number}
```

**Track Order by Email**
```http
POST /api/v1/orders/check
Content-Type: application/json
```

Request Body:
```json
{
  "email": "john@example.com",
  "order_number": "ORD-20240115-ABC123"
}
```

## 🗄️ Database Schema

### Categories
- `id` - Primary key
- `name` - Unique category name
- `slug` - URL identifier (route binding)
- `description` - Category description
- `image` - Image path
- `sort_order` - Display order
- `is_active` - Boolean flag
- `timestamps` - created_at, updated_at
- `soft_deletes` - deleted_at

### Products
- `id` - Primary key
- `category_id` - Foreign key (belongs to Category)
- `name` - Product name
- `slug` - URL identifier
- `description` - Short description
- `details` - Detailed information
- `price` - Decimal (2 places)
- `image` - Image path
- `stock` - Available quantity
- `is_featured` - Boolean flag
- `status` - ENUM: active/inactive
- `view_count` - Analytics counter
- `timestamps` - created_at, updated_at
- `soft_deletes` - deleted_at

### Orders
- `id` - Primary key
- `order_number` - Unique identifier (ORD-YYYYMMDD-XXXXX)
- `customer_name` - Customer name
- `customer_email` - Customer email
- `customer_phone` - Phone number
- `delivery_address` - Delivery location
- `total_price` - Order total
- `status` - ENUM: pending/confirmed/processing/shipped/delivered/cancelled
- `notes` - Special instructions
- `delivered_at` - Delivery timestamp
- `timestamps` - created_at, updated_at
- `soft_deletes` - deleted_at

### OrderItems
- `id` - Primary key
- `order_id` - Foreign key (belongs to Order)
- `product_id` - Foreign key (belongs to Product)
- `product_name` - Snapshot of product name
- `unit_price` - Price at purchase time
- `quantity` - Item quantity
- `subtotal` - quantity × unit_price
- `timestamps` - created_at, updated_at

## 🔐 Security

- ✅ CSRF token protection on all forms
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection with Blade escaping
- ✅ Input validation (StoreOrderRequest)
- ✅ HTTP security headers (Nginx)
- ✅ Environment-based configuration
- ✅ Soft deletes for data integrity
- ✅ Request rate limiting ready

## 📱 Responsive Design

- Mobile-first development approach
- Breakpoints: 320px, 768px, 1024px+
- Tailwind CSS utility-first styling
- Optimized touch interactions

## 🧪 Testing

```bash
cd laravel_app

# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ShopControllerTest.php

# Generate coverage report
php artisan test --coverage

# Run tests in Docker
docker-compose exec app php artisan test
```

## 🐳 Docker

### Development

```bash
# Build and start services
docker-compose up -d

# View logs
docker-compose logs -f app

# Access container shell
docker-compose exec app bash

# Stop services
docker-compose down
```

### Production Deployment

```bash
# Build production image
docker build -t florist-shop:latest .

# Run with persistent storage
docker run -d \
  --name florist-shop \
  -p 80:80 \
  -p 443:443 \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -v florist-db:/app/database \
  -v florist-storage:/app/storage \
  florist-shop:latest
```

## 🚦 CI/CD Pipeline

GitHub Actions automatically runs on push/PR:
- PHP syntax validation
- Laravel migrations test
- Unit & feature tests
- Code style checking (Pint)
- Docker image build
- Security scanning
- Registry push (main branch)

## 📊 Performance

- Gzip compression enabled
- Static assets cached for 1 year
- Database query optimization
- Lazy loading relationships
- Vite bundling optimization
- CDN-ready configuration

## 🌐 Supported Languages

- 🇮🇩 Indonesian (default)
- 🇬🇧 English

Language files: `laravel_app/resources/lang/{locale}`

## 📝 Environment Variables

Key `.env` settings:

```env
APP_NAME="Florist Shop"
APP_ENV=local|production
APP_DEBUG=true|false
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log|smtp
```

## 🔄 API Workflow Example

```bash
# 1. Get categories
curl http://localhost/api/v1/categories

# 2. Get products by category
curl "http://localhost/api/v1/products?category=bouquets"

# 3. Create order
curl -X POST http://localhost/api/v1/orders \
  -H "Content-Type: application/json" \
  -d @order-payload.json

# 4. Track order
curl -X POST http://localhost/api/v1/orders/check \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","order_number":"ORD-..."}'
```

## 🎯 Future Roadmap

- [ ] Payment gateway (Midtrans, Stripe)
- [ ] Advanced admin dashboard
- [ ] Inventory management
- [ ] Customer reviews & ratings
- [ ] Email/SMS notifications
- [ ] Wishlist functionality
- [ ] Discount codes
- [ ] User authentication
- [ ] Analytics dashboard
- [ ] Mobile app (React Native)

## 📞 Support & Contact

For issues, questions, or contributions:
1. Create an issue on GitHub
2. Submit a pull request
3. Contact the development team

## 📄 License

MIT License - See LICENSE file for details

---

**Built with ❤️ using Laravel 13, Vue.js, and Docker**

*Professional semester project for Web Programming course*
