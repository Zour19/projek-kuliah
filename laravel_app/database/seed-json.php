<?php

// Simple JSON-based database for products
// Temporary solution while SQLite extension is being set up

$baseDir = __DIR__;
$dataDir = $baseDir . '/../../data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$productsFile = $dataDir . '/products.json';
$categoriesFile = $dataDir . '/categories.json';

// Categories data
$categories = [
    [
        'id' => 1,
        'name' => 'Bouquets',
        'slug' => 'bouquets',
        'description' => 'Hand-tied bouquets for every occasion.',
        'image' => 'assets/images/bouquets/bouqet.jpeg',
        'sort_order' => 1,
        'is_active' => true,
    ],
    [
        'id' => 2,
        'name' => 'Standing Flowers',
        'slug' => 'standing-flowers',
        'description' => 'Tall floral displays for celebrations and ceremonies.',
        'image' => 'assets/images/standing-flowers/standingbunga.jpeg',
        'sort_order' => 2,
        'is_active' => true,
    ],
    [
        'id' => 3,
        'name' => 'Bloom Box',
        'slug' => 'bloom-box',
        'description' => 'Curated gift boxes with seasonal blooms.',
        'image' => 'assets/images/bloom-box/blumbox.jpg',
        'sort_order' => 3,
        'is_active' => true,
    ],
    [
        'id' => 4,
        'name' => 'Flowers',
        'slug' => 'flowers',
        'description' => 'Fresh cut flowers, ready to arrange.',
        'image' => 'assets/images/flowers/bunga.jpeg',
        'sort_order' => 4,
        'is_active' => true,
    ],
    [
        'id' => 5,
        'name' => 'Accessories',
        'slug' => 'accessories',
        'description' => 'Vases, ribbons, and floral accessories.',
        'image' => 'assets/images/accessories/default.png',
        'sort_order' => 5,
        'is_active' => true,
    ],
];

// Products data
$products = [
    // Bouquets
    [
        'id' => 1,
        'category_id' => 1,
        'name' => 'Romantic Rose Bouquet',
        'slug' => 'romantic-rose-bouquet',
        'description' => 'A romantic rose bouquet with soft pastel petals.',
        'price' => 250000,
        'image' => 'assets/images/bouquets/bouqets1.jpeg',
        'stock' => 50,
        'is_featured' => true,
    ],
    [
        'id' => 2,
        'category_id' => 1,
        'name' => 'Classic Bouquet Collection',
        'slug' => 'classic-bouquet-collection',
        'description' => 'Classic and elegant bouquet for any occasion.',
        'price' => 220000,
        'image' => 'assets/images/bouquets/bouqet.jpeg',
        'stock' => 55,
        'is_featured' => true,
    ],
    [
        'id' => 3,
        'category_id' => 1,
        'name' => 'Premium Bouquet Selection',
        'slug' => 'premium-bouquet-selection',
        'description' => 'Premium selection of the finest flowers.',
        'price' => 350000,
        'image' => 'assets/images/bouquets/bouqets.jpeg',
        'stock' => 35,
        'is_featured' => false,
    ],
    [
        'id' => 4,
        'category_id' => 1,
        'name' => 'Exclusive Bouquet Design',
        'slug' => 'exclusive-bouquet-design',
        'description' => 'Exclusive limited edition bouquet design.',
        'price' => 450000,
        'image' => 'assets/images/bouquets/bouqetsss.jpeg',
        'stock' => 20,
        'is_featured' => true,
    ],

    // Standing Flowers
    [
        'id' => 5,
        'category_id' => 2,
        'name' => 'Royal Standing Arrangement',
        'slug' => 'royal-standing-arrangement',
        'description' => 'An elegant standing flower arrangement for special events.',
        'price' => 400000,
        'image' => 'assets/images/standing-flowers/standingbunga2.jpeg',
        'stock' => 30,
        'is_featured' => true,
    ],
    [
        'id' => 6,
        'category_id' => 2,
        'name' => 'Grand Standing Display',
        'slug' => 'grand-standing-display',
        'description' => 'Impressive grand standing flower display.',
        'price' => 550000,
        'image' => 'assets/images/standing-flowers/standingbunga.jpeg',
        'stock' => 25,
        'is_featured' => true,
    ],
    [
        'id' => 7,
        'category_id' => 2,
        'name' => 'Elegant Standing Arrangement',
        'slug' => 'elegant-standing-arrangement',
        'description' => 'Elegant and refined standing arrangement.',
        'price' => 380000,
        'image' => 'assets/images/standing-flowers/standingbunga3.jpeg',
        'stock' => 28,
        'is_featured' => false,
    ],
    [
        'id' => 8,
        'category_id' => 2,
        'name' => 'Deluxe Standing Arrangement',
        'slug' => 'deluxe-standing-arrangement',
        'description' => 'Deluxe standing arrangement with premium selection.',
        'price' => 600000,
        'image' => 'assets/images/standing-flowers/standingbunga5.jpeg',
        'stock' => 22,
        'is_featured' => true,
    ],
    [
        'id' => 9,
        'category_id' => 2,
        'name' => 'Modern Standing Design',
        'slug' => 'modern-standing-design',
        'description' => 'Modern and contemporary standing flower design.',
        'price' => 420000,
        'image' => 'assets/images/standing-flowers/standingbunga6.jpeg',
        'stock' => 32,
        'is_featured' => false,
    ],

    // Bloom Box
    [
        'id' => 10,
        'category_id' => 3,
        'name' => 'Celebration Bloom Box',
        'slug' => 'celebration-bloom-box',
        'description' => 'Beautiful bloom box with seasonal fresh flowers.',
        'price' => 180000,
        'image' => 'assets/images/bloom-box/box.jpeg',
        'stock' => 40,
        'is_featured' => true,
    ],
    [
        'id' => 11,
        'category_id' => 3,
        'name' => 'Premium Bloom Box',
        'slug' => 'premium-bloom-box',
        'description' => 'Premium bloom box with luxury flowers.',
        'price' => 250000,
        'image' => 'assets/images/bloom-box/blumbox.jpg',
        'stock' => 35,
        'is_featured' => true,
    ],
    [
        'id' => 12,
        'category_id' => 3,
        'name' => 'Deluxe Bloom Box',
        'slug' => 'deluxe-bloom-box',
        'description' => 'Deluxe bloom box with exclusive flowers.',
        'price' => 320000,
        'image' => 'assets/images/bloom-box/blumbox1.jpg',
        'stock' => 28,
        'is_featured' => false,
    ],
    [
        'id' => 13,
        'category_id' => 3,
        'name' => 'Luxury Bloom Box',
        'slug' => 'luxury-bloom-box',
        'description' => 'Luxury bloom box with premium arrangement.',
        'price' => 280000,
        'image' => 'assets/images/bloom-box/box12.jpeg',
        'stock' => 30,
        'is_featured' => false,
    ],
    [
        'id' => 14,
        'category_id' => 3,
        'name' => 'Royal Bloom Box',
        'slug' => 'royal-bloom-box',
        'description' => 'Royal bloom box with premium selection.',
        'price' => 350000,
        'image' => 'assets/images/bloom-box/boxx.jpeg',
        'stock' => 25,
        'is_featured' => true,
    ],

    // Flowers
    [
        'id' => 15,
        'category_id' => 4,
        'name' => 'Signature Floral Bundle',
        'slug' => 'signature-floral-bundle',
        'description' => 'Loose fresh flowers ready for custom arrangements.',
        'price' => 120000,
        'image' => 'assets/images/flowers/bunga12.jpeg',
        'stock' => 60,
        'is_featured' => false,
    ],
    [
        'id' => 16,
        'category_id' => 4,
        'name' => 'Garden Bloom Set',
        'slug' => 'garden-bloom-set',
        'description' => 'Assorted garden flowers with lush greenery.',
        'price' => 135000,
        'image' => 'assets/images/flowers/bunga23.jpeg',
        'stock' => 45,
        'is_featured' => false,
    ],
    [
        'id' => 17,
        'category_id' => 4,
        'name' => 'Premium Fresh Flowers',
        'slug' => 'premium-fresh-flowers',
        'description' => 'Premium fresh cut flowers selection.',
        'price' => 180000,
        'image' => 'assets/images/flowers/bunga.jpeg',
        'stock' => 50,
        'is_featured' => true,
    ],
    [
        'id' => 18,
        'category_id' => 4,
        'name' => 'Exotic Flower Collection',
        'slug' => 'exotic-flower-collection',
        'description' => 'Exotic flowers from around the world.',
        'price' => 220000,
        'image' => 'assets/images/flowers/bunga3.jpeg',
        'stock' => 35,
        'is_featured' => true,
    ],
    [
        'id' => 19,
        'category_id' => 4,
        'name' => 'Seasonal Flower Mix',
        'slug' => 'seasonal-flower-mix',
        'description' => 'Mix of seasonal flowers available now.',
        'price' => 155000,
        'image' => 'assets/images/flowers/bunga5.jpeg',
        'stock' => 55,
        'is_featured' => false,
    ],
    [
        'id' => 20,
        'category_id' => 4,
        'name' => 'Classic Flower Bundle',
        'slug' => 'classic-flower-bundle',
        'description' => 'Classic flowers bundle for any occasion.',
        'price' => 125000,
        'image' => 'assets/images/flowers/bunga21.jpeg',
        'stock' => 65,
        'is_featured' => false,
    ],

    // Accessories
    [
        'id' => 21,
        'category_id' => 5,
        'name' => 'Gift Box & Ribbon Kit',
        'slug' => 'gift-box-ribbon-kit',
        'description' => 'Gift packaging kit with ribbons and decorative accents.',
        'price' => 65000,
        'image' => 'assets/images/accessories/lainya.jpeg',
        'stock' => 100,
        'is_featured' => false,
    ],
    [
        'id' => 22,
        'category_id' => 5,
        'name' => 'Premium Accessories Pack',
        'slug' => 'premium-accessories-pack',
        'description' => 'Premium floral accessories and decorations.',
        'price' => 95000,
        'image' => 'assets/images/accessories/lainya1.jpeg',
        'stock' => 80,
        'is_featured' => false,
    ],
    [
        'id' => 23,
        'category_id' => 5,
        'name' => 'Deluxe Accessory Collection',
        'slug' => 'deluxe-accessory-collection',
        'description' => 'Deluxe collection of floral accessories.',
        'price' => 150000,
        'image' => 'assets/images/accessories/lainya2.jpeg',
        'stock' => 60,
        'is_featured' => true,
    ],
];

// Save to JSON files
file_put_contents($categoriesFile, json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
file_put_contents($productsFile, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "✅ Data tersimpan ke JSON:\n";
echo "   - $categoriesFile\n";
echo "   - $productsFile\n";
echo "\n📊 Summary:\n";
echo "   - " . count($categories) . " Categories\n";
echo "   - " . count($products) . " Products\n";
