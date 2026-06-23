# 🔌 Florist Shop API Documentation

## Overview

RESTful API for Florist Shop e-commerce platform providing endpoints for products, categories, and order management.

**API Base URL:** `/api/v1`

**Content-Type:** `application/json`

**Response Format:** JSON

## Response Structure

### Success Response
```json
{
  "data": [...],
  "message": "Optional success message"
}
```

### Error Response
```json
{
  "message": "Error description",
  "error": "Error details",
  "errors": {
    "field": ["Error message"]
  }
}
```

## HTTP Status Codes
- `200 OK` - Successful GET, PUT request
- `201 Created` - Successful POST request
- `400 Bad Request` - Invalid input
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation error
- `500 Internal Server Error` - Server error

---

## Products API

### 1. List All Products

**Endpoint:**
```http
GET /api/v1/products
```

**Query Parameters:**
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `category` | string | Filter by category slug | `bouquets` |
| `featured` | boolean | Featured products only | `true` |
| `status` | string | Product status | `active` |
| `search` | string | Search in name/description | `rose` |
| `sort` | string | Sort field | `created_at` |
| `order` | string | Sort order | `desc` \| `asc` |
| `per_page` | integer | Results per page (1-100) | `12` |

**Example Requests:**
```bash
# Get all active products
curl "http://localhost/api/v1/products?status=active"

# Get featured products
curl "http://localhost/api/v1/products?featured=true"

# Search products
curl "http://localhost/api/v1/products?search=rose&per_page=20"

# Get bouquets category, sorted by price
curl "http://localhost/api/v1/products?category=bouquets&sort=price&order=asc"
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "name": "Romantic Rose Bouquet",
      "slug": "romantic-rose-bouquet",
      "description": "A romantic rose bouquet with soft pastel petals.",
      "details": "Beautiful arrangement of 24 premium roses...",
      "price": "250000.00",
      "image": "assets/images/bouquets/bouqets1.jpeg",
      "stock": 50,
      "is_featured": true,
      "status": "active",
      "view_count": 125,
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ],
  "links": {
    "first": "http://localhost/api/v1/products?page=1",
    "last": "http://localhost/api/v1/products?page=3",
    "prev": null,
    "next": "http://localhost/api/v1/products?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 12,
    "to": 12,
    "total": 35
  }
}
```

### 2. Get Featured Products

**Endpoint:**
```http
GET /api/v1/products/featured
```

**Description:** Returns up to 6 featured, active products

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Romantic Rose Bouquet",
      "price": "250000.00",
      "image": "assets/images/bouquets/bouqets1.jpeg",
      "is_featured": true
    },
    {
      "id": 3,
      "name": "Celebration Bloom Box",
      "price": "180000.00",
      "image": "assets/images/bloom-box/box.jpeg",
      "is_featured": true
    }
  ]
}
```

### 3. Get Single Product

**Endpoint:**
```http
GET /api/v1/products/{id}
```

**Parameters:**
| Name | Type | Description |
|------|------|-------------|
| `id` | integer | Product ID |

**Note:** Automatically increments product view count

**Example Request:**
```bash
curl http://localhost/api/v1/products/1
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "category_id": 1,
    "category": {
      "id": 1,
      "name": "Bouquets",
      "slug": "bouquets"
    },
    "name": "Romantic Rose Bouquet",
    "slug": "romantic-rose-bouquet",
    "description": "A romantic rose bouquet with soft pastel petals.",
    "details": "Beautiful arrangement of 24 premium roses with premium greens and eucalyptus leaves.",
    "price": "250000.00",
    "image": "assets/images/bouquets/bouqets1.jpeg",
    "stock": 50,
    "is_featured": true,
    "status": "active",
    "view_count": 126,
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:00Z"
  }
}
```

---

## Categories API

### 1. List All Categories

**Endpoint:**
```http
GET /api/v1/categories
```

**Description:** Returns all active categories sorted by order

**Example Request:**
```bash
curl http://localhost/api/v1/categories
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Bouquets",
      "slug": "bouquets",
      "description": "Hand-tied bouquets for every occasion.",
      "image": "assets/images/bouquets/bouqet.jpeg",
      "sort_order": 1,
      "is_active": true,
      "created_at": "2024-01-15T10:00:00Z",
      "updated_at": "2024-01-15T10:00:00Z"
    },
    {
      "id": 2,
      "name": "Standing Flowers",
      "slug": "standing-flowers",
      "description": "Tall floral displays for celebrations and ceremonies.",
      "image": "assets/images/standing-flowers/standingbunga.jpeg",
      "sort_order": 2,
      "is_active": true,
      "created_at": "2024-01-15T10:00:00Z",
      "updated_at": "2024-01-15T10:00:00Z"
    }
  ]
}
```

### 2. Get Category with Products

**Endpoint:**
```http
GET /api/v1/categories/{slug}
```

**Parameters:**
| Name | Type | Description |
|------|------|-------------|
| `slug` | string | Category slug (URL identifier) |

**Example Request:**
```bash
curl http://localhost/api/v1/categories/bouquets
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Bouquets",
    "slug": "bouquets",
    "description": "Hand-tied bouquets for every occasion.",
    "image": "assets/images/bouquets/bouqet.jpeg",
    "active_products": [
      {
        "id": 1,
        "name": "Romantic Rose Bouquet",
        "price": "250000.00",
        "image": "assets/images/bouquets/bouqets1.jpeg"
      },
      {
        "id": 7,
        "name": "Garden Bloom Set",
        "price": "135000.00",
        "image": "assets/images/flowers/bunga23.jpeg"
      }
    ]
  }
}
```

---

## Orders API

### 1. Create Order

**Endpoint:**
```http
POST /api/v1/orders
```

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "+62812345678",
  "delivery_address": "Jl. Merdeka No. 1, Jakarta, Indonesia",
  "notes": "Please wrap nicely for a gift",
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

**Validation Rules:**
| Field | Rules |
|-------|-------|
| `customer_name` | required, string, max 255 |
| `customer_email` | required, email, max 255 |
| `customer_phone` | required, string, max 20 |
| `delivery_address` | required, string, max 1000 |
| `items` | required, array, min 1 item |
| `items.*.product_id` | required, exists in products table |
| `items.*.quantity` | required, integer, min 1, max 100 |
| `notes` | optional, string, max 500 |

**Example Request:**
```bash
curl -X POST http://localhost/api/v1/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "customer_phone": "+62812345678",
    "delivery_address": "Jl. Merdeka No. 1, Jakarta",
    "items": [
      {"product_id": 1, "quantity": 2}
    ]
  }'
```

**Response (201 Created):**
```json
{
  "message": "Order berhasil dibuat",
  "data": {
    "id": 1,
    "order_number": "ORD-20240115-A1B2C3D4",
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "customer_phone": "+62812345678",
    "delivery_address": "Jl. Merdeka No. 1, Jakarta",
    "total_price": "650000.00",
    "status": "pending",
    "notes": "Please wrap nicely for a gift",
    "delivered_at": null,
    "items": [
      {
        "id": 1,
        "order_id": 1,
        "product_id": 1,
        "product_name": "Romantic Rose Bouquet",
        "unit_price": "250000.00",
        "quantity": 2,
        "subtotal": "500000.00"
      },
      {
        "id": 2,
        "order_id": 1,
        "product_id": 3,
        "product_name": "Celebration Bloom Box",
        "unit_price": "150000.00",
        "quantity": 1,
        "subtotal": "150000.00"
      }
    ],
    "created_at": "2024-01-15T10:45:00Z",
    "updated_at": "2024-01-15T10:45:00Z"
  }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "customer_email": ["Format email tidak valid"],
    "items.0.product_id": ["Produk tidak ditemukan"]
  }
}
```

### 2. Get Order Details

**Endpoint:**
```http
GET /api/v1/orders/{order_number}
```

**Parameters:**
| Name | Type | Description |
|------|------|-------------|
| `order_number` | string | Order number (ORD-YYYYMMDD-XXXXX) |

**Example Request:**
```bash
curl http://localhost/api/v1/orders/ORD-20240115-A1B2C3D4
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "order_number": "ORD-20240115-A1B2C3D4",
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "customer_phone": "+62812345678",
    "delivery_address": "Jl. Merdeka No. 1, Jakarta",
    "total_price": "650000.00",
    "status": "confirmed",
    "notes": "Please wrap nicely for a gift",
    "delivered_at": null,
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Romantic Rose Bouquet",
        "unit_price": "250000.00",
        "quantity": 2,
        "subtotal": "500000.00"
      },
      {
        "id": 2,
        "product_id": 3,
        "product_name": "Celebration Bloom Box",
        "unit_price": "150000.00",
        "quantity": 1,
        "subtotal": "150000.00"
      }
    ],
    "created_at": "2024-01-15T10:45:00Z",
    "updated_at": "2024-01-15T11:00:00Z"
  }
}
```

### 3. Track Order by Email

**Endpoint:**
```http
POST /api/v1/orders/check
```

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "order_number": "ORD-20240115-A1B2C3D4"
}
```

**Validation Rules:**
| Field | Rules |
|-------|-------|
| `email` | required, email |
| `order_number` | required, string |

**Example Request:**
```bash
curl -X POST http://localhost/api/v1/orders/check \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "order_number": "ORD-20240115-A1B2C3D4"
  }'
```

**Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "order_number": "ORD-20240115-A1B2C3D4",
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "status": "confirmed",
    "total_price": "650000.00",
    "items": [...]
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "message": "Order tidak ditemukan"
}
```

---

## Order Status Workflow

Orders go through the following status progression:

```
pending → confirmed → processing → shipped → delivered
                                            ↓
                                        cancelled
```

| Status | Description |
|--------|-------------|
| `pending` | Order placed, awaiting confirmation |
| `confirmed` | Order confirmed by shop |
| `processing` | Order being prepared |
| `shipped` | Order sent out for delivery |
| `delivered` | Order delivered to customer |
| `cancelled` | Order cancelled |

---

## Category Slugs

Use these slugs for category filtering:

| Slug | Category |
|------|----------|
| `bouquets` | Bouquets |
| `standing-flowers` | Standing Flowers |
| `bloom-box` | Bloom Box |
| `flowers` | Flowers |
| `accessories` | Accessories |

---

## Common Error Messages

| Error | Cause | Solution |
|-------|-------|----------|
| "Nama pelanggan harus diisi" | Missing customer_name | Provide customer_name |
| "Format email tidak valid" | Invalid email format | Use valid email address |
| "Produk tidak ditemukan" | Invalid product_id | Check product exists |
| "Jumlah minimal 1" | Quantity < 1 | Use quantity >= 1 |
| "Order tidak ditemukan" | Wrong email/order_number | Verify correct details |

---

## Rate Limiting

Currently no rate limiting. Production deployment should implement:
- 100 requests per minute per IP
- 1000 requests per hour per IP

---

## CORS

CORS is configured for:
- Allowed Origins: Configured in Laravel
- Allowed Methods: GET, POST, PUT, DELETE, OPTIONS
- Allowed Headers: Content-Type, Authorization

---

## Examples

### Complete Order Workflow

```bash
# 1. Get categories
curl http://localhost/api/v1/categories

# 2. Get products in Bouquets category
curl "http://localhost/api/v1/products?category=bouquets"

# 3. Get product details
curl http://localhost/api/v1/products/1

# 4. Create order
curl -X POST http://localhost/api/v1/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "customer_phone": "+628123456789",
    "delivery_address": "Jl. Merdeka No. 1",
    "items": [{"product_id": 1, "quantity": 1}]
  }'

# 5. Check order status (using response order_number)
curl -X POST http://localhost/api/v1/orders/check \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "order_number": "ORD-20240115-ABC123"
  }'
```

---

## Changelog

### v1.0.0 (2024-01-15)
- Initial API release
- Products, Categories, Orders endpoints
- Order tracking by email
- Product filtering and search

---

## Support

For API issues or questions:
1. Check the examples section
2. Review validation rules
3. Check error response format
4. Create GitHub issue with:
   - API endpoint used
   - Request/response body
   - Error message
   - Environment (local/docker/production)
