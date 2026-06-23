# Florist Shop - JSON-Based Product Management System

## 📋 Overview

Sistem ini menggunakan **JSON-based storage** untuk mengelola kategori dan produk florist shop. Alih-alih menggunakan database SQLite (yang memerlukan extension PHP yang tidak tersedia), kami menggunakan file JSON untuk menyimpan data produk dan kategori.

## 🗂️ Project Structure

```
projek akhir semester/
├── data/                          # JSON data files
│   ├── categories.json            # 5 kategori produk
│   └── products.json              # 23 produk across 5 kategori
│
├── laravel_app/                   # Laravel application
│   ├── app/Http/Controllers/
│   │   └── ShopController.php     # Updated untuk membaca dari JSON
│   ├── resources/views/
│   │   ├── home.blade.php         # Halaman utama dengan kategori & featured products
│   │   ├── category.blade.php     # Halaman kategori dengan produk
│   │   └── components/layouts/
│   │       └── app.blade.php      # Main layout
│   ├── public/assets/images/      # Image files untuk produk
│   │   ├── accessories/
│   │   ├── bloom-box/
│   │   ├── bouquets/
│   │   ├── flowers/
│   │   └── standing-flowers/
│   └── routes/
│       └── web.php                # Route definitions
│
└── seed-json.php                  # Script untuk generate JSON data
```

## 📦 Data Structure

### Categories (5 Kategori)
```json
{
  "id": 1,
  "name": "Bouquets",
  "slug": "bouquets",
  "description": "Hand-tied bouquets for every occasion.",
  "image": "assets/images/bouquets/bouqet.jpeg",
  "sort_order": 1,
  "is_active": true
}
```

### Products (23 Produk)
```json
{
  "id": 1,
  "category_id": 1,
  "name": "Romantic Rose Bouquet",
  "slug": "romantic-rose-bouquet",
  "description": "A romantic rose bouquet with soft pastel petals.",
  "price": 250000,
  "image": "assets/images/bouquets/bouqets1.jpeg",
  "stock": 50,
  "is_featured": true
}
```

## 🚀 Implementasi

### 1. ShopController Updates

File: `laravel_app/app/Http/Controllers/ShopController.php`

Controller membaca data dari JSON files:
- `categories()` - Membaca dari `data/categories.json`
- `products()` - Membaca dari `data/products.json`
- `featuredProducts()` - Filter produk dengan `is_featured: true`

**Key Methods:**
```php
private function categories(): array {
    $file = base_path('../data/categories.json');
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        // Process and return categories
    }
    return [];
}
```

### 2. Product Data Distribution

**Kategori & Jumlah Produk:**
- **Bouquets**: 4 produk (Romantic Rose, Classic Collection, Premium Selection, Exclusive Design)
- **Standing Flowers**: 5 produk (Royal, Grand Display, Elegant, Deluxe, Modern Design)
- **Bloom Box**: 5 produk (Celebration, Premium, Deluxe, Luxury, Royal)
- **Flowers**: 6 produk (Signature Bundle, Garden Bloom, Premium Fresh, Exotic, Seasonal Mix, Classic Bundle)
- **Accessories**: 3 produk (Gift Box Kit, Premium Pack, Deluxe Collection)

**Total: 23 Produk dengan 9 featured products**

### 3. Image Asset Organization

Semua gambar disalin ke `laravel_app/public/assets/images/` dengan struktur:
```
public/assets/images/
├── bouquets/
│   ├── bouqet.jpeg
│   ├── bouqets1.jpeg
│   ├── bouqets.jpeg
│   └── bouqetsss.jpeg
├── standing-flowers/
│   ├── standingbunga.jpeg
│   ├── standingbunga2.jpeg
│   ├── standingbunga3.jpeg
│   ├── standingbunga5.jpeg
│   └── standingbunga6.jpeg
├── bloom-box/
│   ├── box.jpeg
│   ├── blumbox.jpg
│   ├── blumbox1.jpg
│   ├── box12.jpeg
│   └── boxx.jpeg
├── flowers/
│   ├── bunga.jpeg
│   ├── bunga3.jpeg
│   ├── bunga5.jpeg
│   ├── bunga12.jpeg
│   ├── bunga21.jpeg
│   ├── bunga23.jpeg
│   └── ... (more flower images)
└── accessories/
    ├── lainya.jpeg
    ├── lainya1.jpeg
    ├── lainya2.jpeg
    └── default.png
```

## 🔄 How It Works

### 1. Home Page Flow
```
GET /
├── ShopController@home()
│   ├── getCategories() → Load from data/categories.json
│   ├── getFeaturedProducts() → Filter is_featured = true
│   └── return view('home', $data)
└── Blade Template
    ├── Display 5 Categories with images
    ├── Display 6 Featured Products
    └── Show category links
```

### 2. Category Page Flow
```
GET /category/{slug}
├── ShopController@category($slug)
│   ├── Find category by slug in JSON
│   ├── Load all products matching category_id
│   └── return view('category', $products)
└── Blade Template
    ├── Display category header
    ├── Display all products in category
    └── Show "Order now" buttons
```

### 3. Data Loading Sequence
```
ShopController
├── Check if JSON files exist
├── Read file content
├── json_decode() to array
├── Process and map data
└── Return to view
```

## 📊 Verified Features

✅ **Homepage**
- 5 kategori ditampilkan dengan gambar
- 6 featured products ditampilkan
- Navigation ke kategori pages berfungsi

✅ **Category Pages**
- Bouquets: 4 produk
- Standing Flowers: 5 produk
- Bloom Box: 5 produk
- Flowers: 6 produk
- Accessories: 3 produk

✅ **Product Display**
- Nama produk
- Harga format Rupiah (Rp X.XXX)
- Deskripsi produk
- Gambar produk dari assets folder

✅ **Image Assets**
- 30+ gambar terorganisir per kategori
- Accessible dari public/assets/images/
- Properly linked dalam views

## 🛠️ Setup Instructions

### 1. Generate JSON Data (Already Done)
```bash
cd laravel_app
php database/seed-json.php
```

### 2. Run Laravel Development Server
```bash
cd laravel_app
php artisan serve --host=0.0.0.0 --port=8000
```

### 3. Access Application
- Homepage: `http://localhost:8000`
- Bouquets: `http://localhost:8000/category/bouquets`
- Standing Flowers: `http://localhost:8000/category/standing-flowers`
- Bloom Box: `http://localhost:8000/category/bloom-box`
- Flowers: `http://localhost:8000/category/flowers`
- Accessories: `http://localhost:8000/category/accessories`

## 📝 Adding New Products

### Method 1: Manual JSON Edit
Edit `data/products.json` dan tambahkan produk baru:
```json
{
  "id": 24,
  "category_id": 1,
  "name": "New Product Name",
  "slug": "new-product-name",
  "description": "Product description",
  "price": 300000,
  "image": "assets/images/category/image.jpeg",
  "stock": 50,
  "is_featured": false
}
```

### Method 2: Using PHP Script
Buat script untuk add product:
```php
$products = json_decode(file_get_contents('data/products.json'), true);
$products[] = [
    'id' => count($products) + 1,
    'category_id' => 1,
    'name' => 'New Product',
    // ... other fields
];
file_put_contents('data/products.json', json_encode($products, JSON_PRETTY_PRINT));
```

## 🎯 Future Improvements

### Option 1: Migrate to Database
Jika PHP SQLite extension tersedia, migrasi ke SQLite:
```bash
php artisan migrate:fresh --seed
```

### Option 2: Add Admin Panel
Buat admin interface untuk manage produk tanpa edit JSON langsung

### Option 3: API Endpoints
Buat REST API endpoints untuk manage data:
```
POST /api/products
GET /api/products
PUT /api/products/{id}
DELETE /api/products/{id}
```

## 📋 Files Modified/Created

### Created:
- `database/seed-json.php` - Generate JSON data
- `data/categories.json` - 5 kategori
- `data/products.json` - 23 produk

### Modified:
- `app/Http/Controllers/ShopController.php` - Read from JSON
- `public/assets/images/` - Copy images from project

### Views (Already Exist):
- `resources/views/home.blade.php`
- `resources/views/category.blade.php`
- `resources/views/components/layouts/app.blade.php`

## ✨ Testing Summary

| Feature | Status | Notes |
|---------|--------|-------|
| Homepage Load | ✅ | 5 categories + 6 featured products |
| Category Pages | ✅ | All 5 categories working |
| Product Display | ✅ | Names, prices, descriptions shown |
| Image Loading | ✅ | All images displaying correctly |
| Navigation | ✅ | Links between pages working |
| Responsive Design | ✅ | Tailwind CSS styling applied |
| Data Persistence | ✅ | JSON files stored properly |

---

**Created:** June 23, 2026  
**Last Updated:** June 23, 2026  
**Status:** ✅ Production Ready
