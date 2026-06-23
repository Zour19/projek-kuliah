<?php
declare(strict_types=1);
require_once __DIR__ . '/db-fallback.php';

// Development: show and log PHP errors to help diagnose HTTP 500 issues.
// Remove or disable in production.
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php-error.log');

session_start();

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$pages = [
    'home', 'blogs', 'create-account', 'login', 'reset-password', 'contact-us', 'our-story',
    'bouquet', 'bloom-box', 'flowers', 'standing-flowers', 'accessories', 'cart', 'checkout',
    'admin-login', 'admin-dashboard', 'add-product', 'admin-products', 'admin-assets'
];
if (!in_array($page, $pages, true)) {
    $page = 'home';
}

$categories = get_all_categories();
$category = get_category_by_slug($page);
$products = [];
if ($category) {
    $products = get_products_by_category_id((int)$category['id']);
}
$featuredProducts = [];
$bouquetCategory = get_category_by_slug('bouquet');
if ($bouquetCategory) {
  $featuredProducts = get_products_by_category_id((int)$bouquetCategory['id']);
}
$allProducts = [];
$adminError = '';
$adminSuccess = '';
// Ensure asset sort results variable exists to avoid undefined notices.
$assetSortResults = [];
if ($page === 'admin-products' && is_admin_logged_in()) {
    $allProducts = get_all_products();
}

function navItem(string $slug, string $label, string $current): string
{
    $active = $slug === $current ? ' active' : '';
    return "<a class=\"nav-link{$active}\" href=\"?page={$slug}\">{$label}</a>";
}

function formatPrice(int $value): string
{
    return 'Rp ' . number_format($value, 0, ',', '.');
}
function normalize_upload_files(array $filePost): array
{
    $files = [];
    $fileCount = is_array($filePost['name']) ? count($filePost['name']) : 0;

    for ($i = 0; $i < $fileCount; $i += 1) {
        $files[] = [
            'name' => $filePost['name'][$i],
            'type' => $filePost['type'][$i],
            'tmp_name' => $filePost['tmp_name'][$i],
            'error' => $filePost['error'][$i],
            'size' => $filePost['size'][$i],
        ];
    }

    return $files;
}
function pageTitle(string $page): string
{
    $titles = [
        'home' => 'Matahari Florist',
        'blogs' => 'Blogs',
        'create-account' => 'Create Account',
        'login' => 'Login',
        'reset-password' => 'Reset Password',
        'contact-us' => 'Contact Us',
        'our-story' => 'Our Story',
        'cart' => 'Keranjang Pesanan',
        'checkout' => 'Proses Pembayaran',
        'admin-login' => 'Admin Login',
        'admin-dashboard' => 'Admin Dashboard',
        'add-product' => 'Tambah Produk',
        'admin-products' => 'Daftar Produk',
        'admin-assets' => 'Asset Sorter',
    ];

    if (isset($titles[$page])) {
        return $titles[$page];
    }

    $category = get_category_by_slug($page);
    if ($category) {
        return $category['name'];
    }

    return 'Matahari Florist';
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'admin-login') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $user = get_user_by_username($username);
        if ($user && $user['role'] === 'admin' && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: ?page=admin-dashboard');
            exit;
        }
        $adminError = 'Login admin gagal. Username atau password salah.';
    }

    if ($page === 'add-product' && is_admin_logged_in()) {
        $name = trim((string) ($_POST['name'] ?? ''));
        $price = (int) ($_POST['price'] ?? 0);
        $categorySlug = trim((string) ($_POST['category'] ?? ''));
        $image = trim((string) ($_POST['image'] ?? ''));

        $categoryRow = get_category_by_slug($categorySlug);
        if (!$name || $price <= 0 || !$categoryRow) {
            $adminError = 'Nama produk, harga, dan kategori harus diisi dengan benar.';
        } else {
            $imagePath = '';
            if (!empty($_FILES['image_upload']['tmp_name']) && is_uploaded_file($_FILES['image_upload']['tmp_name'])) {
                $upload = $_FILES['image_upload'];
                $maxSize = (int) config('UPLOAD_MAX_SIZE', 1048576);
                $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                if ($upload['error'] !== UPLOAD_ERR_OK) {
                    $adminError = 'Gagal mengunggah gambar. Coba lagi.';
                } elseif ($upload['size'] > $maxSize) {
                    $adminError = 'Ukuran gambar terlalu besar. Maksimum ' . number_format($maxSize) . ' byte.';
                } else {
                    $extension = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
                    if (!in_array($extension, $allowedExt, true)) {
                        $adminError = 'Tolong unggah file gambar dengan format JPG, PNG, WEBP, atau GIF.';
                    } else {
                        $uploadFolder = __DIR__ . '/' . ltrim($categoryRow['image_folder'], '/');
                        if (!is_dir($uploadFolder)) {
                            mkdir($uploadFolder, 0755, true);
                        }
                        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($upload['name']));
                        $filename = time() . '-' . $safeName;
                        $targetPath = $uploadFolder . '/' . $filename;
                        if (move_uploaded_file($upload['tmp_name'], $targetPath)) {
                            $imagePath = $categoryRow['image_folder'] . '/' . $filename;
                        } else {
                            $adminError = 'Gagal memindahkan file gambar.';
                        }
                    }
                }
            }

            if (!$adminError) {
                $imagePath = $imagePath !== '' ? $imagePath : ($image !== '' ? $image : ($categoryRow['image_folder'] . '/default.png'));
                if (add_product((int)$categoryRow['id'], $name, $price, $imagePath)) {
                    $adminSuccess = 'Produk berhasil ditambahkan.';
                    $products = get_products_by_category_id((int)$categoryRow['id']);
                } else {
                    $adminError = 'Gagal menambahkan produk. Coba lagi.';
                }
            }
        }
    }
}

$blogPosts = [
    [
        'title' => 'Cara Agar Bunga Awet Sampai 2 Minggu',
        'text' => 'Sayang banget kan kalau bunga segar pemberian si dia cepat layu? Yuk simak langkah mudah perawatannya di sini!',
        'image' => 'assets/guideline/Blogs.png',
    ],
    [
        'title' => 'Tidak Hanya Merah, Ini Makna Tersembunyi dari Mawar Kuning',
        'text' => 'Mawar kuning punya pesan rahasia yang tak kalah manis. Pelajari makna di balik kelopak cerah ini.',
        'image' => 'assets/guideline/Blogs.png',
    ],
    [
        'title' => 'Ide Kado Hari Ibu: Apakah Bunga Termasuk Cocok?',
        'text' => 'Masih bingung memilih hadiah? Rekomendasi bunga terbaik yang melambangkan hangatnya kasih sayang ibu.',
        'image' => 'assets/guideline/Blogs.png',
    ],
    [
        'title' => 'Tren Buket Pengantin Kekinian: 2 Warna Makin Diminati',
        'text' => 'Saat ini buket dua warna dengan kontras lembut semakin jadi primadona calon pengantin.',
        'image' => 'assets/guideline/Blogs.png',
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(pageTitle($page)) ?> — Matahari Florist</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #f7f3ed;
      --surface: #ffffff;
      --text: #1f1b18;
      --muted: #6e645b;
      --accent: #d8b657;
      --accent-dark: #ba9d45;
      --border: rgba(34, 34, 34, 0.08);
      --shadow: 0 24px 40px rgba(39, 30, 24, 0.08);
      --radius: 28px;
      --radius-sm: 14px;
    }
    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
      margin: 0;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text);
    }
    img { max-width: 100%; display: block; }
    a { color: inherit; text-decoration: none; }
    button { font: inherit; }
    .page { max-width: 1180px; margin: 0 auto; padding: 0 24px 48px; }
    .site-header {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 36px;
      padding: 28px 0 12px;
    }
    .brand {
      font-family: 'Playfair Display', serif;
      font-size: 1.65rem;
      letter-spacing: .03em;
      font-weight: 700;
      cursor: pointer;
    }
    .nav {
      display: flex;
      gap: 24px;
      flex-wrap: wrap;
      justify-content: center;
      margin: 18px auto 0;
    }
    .nav-link {
      font-size: .82rem;
      font-weight: 600;
      letter-spacing: .2em;
      text-transform: uppercase;
      color: var(--muted);
      transition: color .2s;
    }
    .nav-link:hover,
    .nav-link.active { color: var(--text); }
    .page-title {
      text-align: center;
      margin: 68px auto 24px;
      max-width: 720px;
    }
    .page-title h1 { margin: 0; }
    .page-title p { color: var(--muted); font-size: 1rem; margin: 18px auto 0; max-width: 600px; line-height: 1.8; }
    .hero {
      display: grid;
      grid-template-columns: 1.05fr .95fr;
      align-items: center;
      gap: 36px;
      margin-top: 48px;
    }
    .hero-copy { max-width: 560px; }
    .hero-subtitle {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-size: .82rem;
      letter-spacing: .16em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 20px;
    }
    .hero-copy h2 {
      font-family: 'Playfair Display', serif;
      font-size: clamp(3rem, 4vw, 4.8rem);
      margin: 0 0 22px;
      line-height: .95;
    }
    .hero-copy p { color: var(--muted); font-size: 1rem; line-height: 1.8; margin-bottom: 28px; }
    .hero-actions { display: flex; flex-wrap: wrap; gap: 18px; }
    .button-primary,
    .button-secondary {
      border: none;
      border-radius: 999px;
      cursor: pointer;
      font-weight: 600;
      letter-spacing: .02em;
      transition: transform .2s, background .2s;
    }
    .button-primary {
      background: var(--accent);
      color: #fff;
      padding: 14px 30px;
    }
    .button-primary:hover { transform: translateY(-2px); background: var(--accent-dark); }
    .button-secondary {
      background: rgba(255,255,255,.96);
      color: var(--text);
      box-shadow: 0 10px 30px rgba(45, 35, 25, .08);
      padding: 14px 30px;
      border: 1px solid var(--border);
    }
    .button-secondary:hover { transform: translateY(-2px); }
    .hero-visual {
      position: relative;
      border-radius: 32px;
      overflow: hidden;
      box-shadow: var(--shadow);
      min-height: 520px;
      background: #f0e9df;
    }
    .hero-visual img { width: 100%; height: 100%; object-fit: cover; }
    .catalog-grid,
    .blog-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; }
    .product-card,
    .blog-card,
    .form-panel,
    .summary-panel,
    .content-panel {
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 18px 45px rgba(48, 34, 20, .06);
      overflow: hidden;
    }
    .product-card .product-image,
    .blog-card img { width: 100%; height: 240px; object-fit: cover; background: linear-gradient(135deg, #f0d9a0, #f8f4ed); }
    .product-card .product-image { display: grid; place-items: center; }
    .card-body { padding: 24px; }
    .product-card h3,
    .content-panel h2,
    .blog-card h3 { margin: 0 0 14px; font-size: 1.2rem; }
    .product-card p,
    .content-panel p,
    .blog-card p,
    .summary-panel p { margin: 0; color: var(--muted); line-height: 1.75; font-size: .95rem; }
    .price-strip {
      background: #f5e4b8;
      color: #3f2f1c;
      padding: 16px 20px;
      font-weight: 600;
      font-size: .99rem;
      text-align: center;
    }
    .form-panel { max-width: 520px; margin: 0 auto; }
    .panel-body { padding: 40px 38px 42px; }
    .form-panel h2 { margin: 0 0 10px; font-size: 2rem; }
    .section-admin-assets { max-width: 720px; margin: 0 auto; }
    .upload-form { display: grid; gap: 18px; }
    .upload-drop-area {
      border: 2px dashed var(--border);
      border-radius: 24px;
      padding: 36px 24px;
      text-align: center;
      background: #fbf7ef;
      cursor: pointer;
      transition: border-color .2s, background .2s, transform .2s;
    }
    .upload-drop-area.highlight { border-color: var(--accent-dark); background: #fff6dc; transform: scale(1.01); }
    .upload-drop-area p { margin: 0 0 10px; color: var(--muted); }
    .upload-drop-area button { margin-top: 10px; }
    .asset-sort-results { background: #fff; border-radius: 24px; padding: 24px; }
    .asset-sort-results h3 { margin-top: 0; }
    .asset-sort-results ul { margin: 16px 0 0; padding-left: 18px; color: var(--text); }
    .field { margin-bottom: 18px; }
    .field label { display: block; margin-bottom: 10px; font-size: .9rem; color: var(--muted); }
    .field input,
    .field select { width: 100%; padding: 16px 18px; font-size: 1rem; border: 1px solid var(--border); border-radius: 14px; outline: none; }
    .form-actions { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-top: 10px; }
    .text-link { color: var(--text); font-weight: 600; }
    .text-link:hover { text-decoration: underline; }
    .wide-button { width: 100%; padding: 14px 20px; border-radius: 14px; border: none; font-weight: 600; cursor: pointer; }
    .wide-button.primary { background: var(--accent); color: #fff; }
    .wide-button.secondary { background: #fff; border: 1px solid var(--border); }
    .content-panel { padding: 24px; }
    .admin-message { margin-bottom: 20px; padding: 16px 20px; border-radius: 18px; }
    .admin-success { background: #e8f7e9; color: #1f5f32; }
    .admin-error { background: #f9e4e4; color: #7d2222; }
    .summary-panel table { width: 100%; border-collapse: collapse; }
    .summary-panel th,
    .summary-panel td { padding: 14px 0; border-bottom: 1px solid rgba(45, 35, 25, .08); text-align: left; font-size: .95rem; color: var(--muted); }
    .summary-panel th { color: var(--text); }
    .summary-panel .total-row td { font-weight: 700; color: var(--text); }
    .footer { display: grid; grid-template-columns: repeat(4, minmax(200px, 1fr)); gap: 28px; margin-top: 64px; padding: 36px 0 0; border-top: 1px solid rgba(36, 28, 20, .12); color: var(--muted); }
    .footer h3 { margin: 0 0 16px; font-size: .82rem; text-transform: uppercase; letter-spacing: .16em; color: var(--text); }
    .footer a:hover { color: var(--text); }
    .whatsapp-fab { position: fixed; right: 24px; bottom: 24px; width: 56px; height: 56px; border-radius: 50%; background: #25d366; color: #fff; display: grid; place-items: center; text-decoration: none; box-shadow: 0 24px 38px rgba(37, 211, 102, .24); font-size: 1.5rem; }
    @media (max-width: 960px) { .hero, .footer { grid-template-columns: 1fr; } .nav { justify-content: center; } }
    @media (max-width: 720px) { .page { padding: 0 16px 32px; } .site-header { flex-direction: column; gap: 12px; } .catalog-grid, .blog-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <main class="page">
    <header class="site-header">
      <div class="brand">Matahari Florist</div>
      <div class="header-actions">
        <button aria-label="Account">👤</button>
        <button aria-label="Cart">🛒</button>
      </div>
    </header>
    <nav class="nav">
      <?= navItem('home', 'Home', $page) ?>
      <?= navItem('bouquet', 'Bouquets', $page) ?>
      <?= navItem('bloom-box', 'Bloom Boxes', $page) ?>
      <?= navItem('flowers', 'Flowers', $page) ?>
      <?= navItem('standing-flowers', 'Standing Flowers', $page) ?>
      <?= navItem('accessories', 'Accessories', $page) ?>
      <?= navItem('blogs', 'Blogs', $page) ?>
      <?= navItem('our-story', 'Our Story', $page) ?>
      <?= navItem('contact-us', 'Contact Us', $page) ?>
    </nav>

    <?php if ($page === 'home'): ?>
      <section class="hero">
        <div class="hero-copy">
          <div class="hero-subtitle">Featured Collection</div>
          <h2>Basket & Bouquet for every special moment</h2>
          <p>Temukan rangkaian bunga segar yang dirangkai khusus untuk momen romantis, ulang tahun, syukuran, atau kejutan sederhana untuk orang terkasih.</p>
          <div class="hero-actions">
            <a class="button-primary" href="?page=bouquet">Shop Bouquets</a>
            <a class="button-secondary" href="?page=bloom-box">Browse Bloom Boxes</a>
          </div>
        </div>
        <div class="hero-visual">
          <img src="assets/hero.png" alt="Matahari Florist hero">
        </div>
      </section>
      <section class="page-title">
        <h1>Highlight collection</h1>
        <p>Rangkaian terbaik kami yang paling sering dipesan, dikemas rapi, dan siap untuk dikirim.</p>
      </section>
      <section class="catalog-grid">
        <?php foreach ($featuredProducts as $item): ?>
          <article class="product-card">
            <div class="product-image" style="background-image: url('<?= htmlspecialchars($item['image'] ?: 'assets/hero.png') ?>'); background-size: cover;"></div>
            <div class="card-body">
              <h3><?= htmlspecialchars($item['name']) ?></h3>
              <div class="price-strip"><?= formatPrice((int)$item['price']) ?></div>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    <?php elseif ($page === 'blogs'): ?>
      <section class="page-title">
        <h1>Blogs</h1>
        <p>Kumpulan artikel inspirasi, tips menyimpan bunga, dan cerita dari dunia florist.</p>
      </section>
      <section class="blog-grid">
        <?php foreach ($blogPosts as $post): ?>
          <article class="blog-card">
            <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
            <div class="card-body">
              <h3><?= htmlspecialchars($post['title']) ?></h3>
              <p><?= htmlspecialchars($post['text']) ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    <?php elseif ($page === 'create-account'): ?>
      <section class="page-title">
        <h1>Create Account</h1>
        <p>Sudah punya akun? <a class="text-link" href="?page=login">Sign in here.</a></p>
      </section>
      <section class="form-panel">
        <div class="panel-body">
          <h2>Create Account</h2>
          <p>Daftar sekarang untuk menikmati layanan pengiriman bunga cepat dan personalisasi ucapan gratis.</p>
          <form id="create-account-form">
            <div class="field"><label for="account-username">Username</label><input id="account-username" name="username" type="text" placeholder="Username" required></div>
            <div class="field"><label for="account-email">Email</label><input id="account-email" name="email" type="email" placeholder="Email" required></div>
            <div class="field"><label for="account-password">Password</label><input id="account-password" name="password" type="password" placeholder="Password" required></div>
            <button class="wide-button primary" type="submit">Create Account</button>
            <div id="create-account-message" style="margin-top:18px;"></div>
          </form>
        </div>
      </section>
    <?php elseif ($page === 'login'): ?>
      <section class="page-title">
        <h1>Login</h1>
        <p>Don't have an account? <a class="text-link" href="?page=create-account">Sign up here.</a></p>
      </section>
      <section class="form-panel">
        <div class="panel-body">
          <h2>Login</h2>
          <p>Masuk untuk melihat status pesanan Anda dan simpan detail checkout lebih cepat.</p>
          <form>
            <div class="field"><label for="login-email">Email</label><input id="login-email" type="email" placeholder="Email"></div>
            <div class="field"><label for="login-password">Password</label><input id="login-password" type="password" placeholder="Password"></div>
            <div class="form-actions">
              <button class="wide-button primary" type="button">Sign in</button>
              <a class="text-link" href="?page=reset-password">Forgot your password?</a>
            </div>
            <div style="margin-top:24px;"><a class="button-secondary" href="?page=home" style="display:inline-flex;">Return to store</a></div>
          </form>
        </div>
      </section>
    <?php elseif ($page === 'reset-password'): ?>
      <section class="page-title">
        <h1>Reset Your Password</h1>
        <p>We will send you an email to reset your password.</p>
      </section>
      <section class="form-panel">
        <div class="panel-body">
          <form>
            <div class="field"><label for="reset-email">Email</label><input id="reset-email" type="email" placeholder="Email"></div>
            <div class="form-actions">
              <button class="wide-button primary" type="button">Submit</button>
              <a class="wide-button secondary" href="?page=login">Cancel</a>
            </div>
          </form>
        </div>
      </section>
    <?php elseif ($page === 'contact-us'): ?>
      <section class="page-title">
        <h1>Contact Us</h1>
        <p>Butuh bantuan atau ingin konsultasi rangkaian bunga terbaik? Hubungi kami atau kunjungi toko.</p>
      </section>
      <div class="content-panel"><div class="panel-body"><p>Alamat: Jl. Sulaiman No.12A 10, Sukabumi Utara, Jakarta Barat</p><p>Telp/WA: +6282122490002</p><p>Email: admin@matahariflorist.com</p></div></div>
    <?php elseif ($page === 'our-story'): ?>
      <section class="page-title">
        <h1>Our Story</h1>
        <p>Kisah singkat Matahari Florist dan bagaimana kami melayani setiap momen dengan kehangatan dan kualitas bunga terbaik.</p>
      </section>
      <div class="content-panel"><div class="panel-body"><h2>From local garden to your hands</h2><p>Kami memulai perjalanan karena cinta akan bunga segar yang bisa memeriahkan suasana hati. Setiap rangkaian dirakit oleh tim florist berpengalaman dengan detail personal yang membuat hadiah Anda tetap berkesan.</p><h2>Committed to quality</h2><p>Kualitas dan kesegaran selalu menjadi prioritas. Bunga kami dipilih setiap hari, disimpan dengan suhu ideal, dan dikirimkan dengan kemasan elegan agar sampai tujuan dalam kondisi prima.</p></div></div>
    <?php elseif ($page === 'cart'): ?>
      <section class="page-title"><h1>Keranjang Pesanan</h1><p>Review item Anda sebelum lanjut ke proses pembayaran. Pastikan alamat dan jumlah sudah sesuai.</p></section>
      <section class="summary-panel"><div class="panel-body"><table><thead><tr><th>Nama Produk</th><th>Qty</th><th>Harga</th></tr></thead><tbody><?php $cartItems = [['name'=>'Golden Rays Bouquet','price'=>469000,'qty'=>1],['name'=>'Calla Lily Bouquet','price'=>599000,'qty'=>1]]; foreach ($cartItems as $item): ?><tr><td><?= htmlspecialchars($item['name']) ?></td><td><?= $item['qty'] ?></td><td><?= formatPrice($item['price']) ?></td></tr><?php endforeach; ?><tr class="total-row"><td>Total</td><td></td><td><?= formatPrice(array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $cartItems))) ?></td></tr></tbody></table><div class="form-actions" style="margin-top:24px;"><a class="wide-button primary" href="?page=checkout">Checkout</a></div></div></section>
    <?php elseif ($page === 'checkout'): ?>
      <section class="page-title"><h1>Proses Pembayaran</h1><p>Isi data pembayaran dan alamat pengiriman Anda untuk menyelesaikan pesanan.</p></section>
      <div class="content-panel"><div class="panel-body"><h2>Payment details</h2><form><div class="field"><label for="card-name">Cardholder Name</label><input id="card-name" type="text" placeholder="Nama pada kartu"></div><div class="field"><label for="card-number">Card Number</label><input id="card-number" type="text" placeholder="1234 5678 9123 4567"></div><div class="field"><label for="expiry">Expiry Date</label><input id="expiry" type="text" placeholder="MM/YY"></div><div class="field"><label for="cvc">CVC</label><input id="cvc" type="text" placeholder="CVC"></div><button class="wide-button primary" type="button">Pay Now</button></form></div></div>
    <?php elseif ($page === 'admin-login'): ?>
      <section class="page-title"><h1>Admin Login</h1><p>Gunakan akun admin untuk menambah produk buket dan katalog.</p></section>
      <section class="form-panel"><div class="panel-body"><?php if ($adminError): ?><div class="admin-message admin-error"><?= htmlspecialchars($adminError) ?></div><?php endif; ?><form method="post"><div class="field"><label for="admin-username">Username</label><input id="admin-username" name="username" type="text" required></div><div class="field"><label for="admin-password">Password</label><input id="admin-password" name="password" type="password" required></div><button class="wide-button primary" type="submit">Login Admin</button></form></div></section>
    <?php elseif ($page === 'admin-dashboard'): ?>
      <?php if (!is_admin_logged_in()): header('Location: ?page=admin-login'); exit; endif; ?>
      <section class="page-title"><h1>Admin Dashboard</h1><p>Selamat datang admin. Tambah produk baru atau kelola koleksi bunga.</p></section>
      <div class="content-panel"><div class="panel-body"><h2>Kategori Produk</h2><ul><?php foreach ($categories as $cat): ?><li><a href="?page=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a></li><?php endforeach; ?></ul><p><a class="button-secondary" href="?page=add-product">Tambah Produk Baru</a></p><p style="margin-top:14px;"><a class="button-secondary" href="?page=admin-products">Lihat Semua Produk</a></p><p style="margin-top:14px;"><a class="button-secondary" href="?page=admin-assets">Asset Sorter</a></p></div></div>
    <?php elseif ($page === 'add-product'): ?>
      <?php if (!is_admin_logged_in()): header('Location: ?page=admin-login'); exit; endif; ?>
      <section class="page-title"><h1>Tambah Produk</h1><p>Tambah buket atau produk baru ke katalog dengan mudah.</p></section>
      <section class="form-panel"><div class="panel-body"><?php if ($adminError): ?><div class="admin-message admin-error"><?= htmlspecialchars($adminError) ?></div><?php endif; ?><?php if ($adminSuccess): ?><div class="admin-message admin-success"><?= htmlspecialchars($adminSuccess) ?></div><?php endif; ?><form method="post" enctype="multipart/form-data"><div class="field"><label for="product-name">Nama Produk</label><input id="product-name" name="name" type="text" required></div><div class="field"><label for="product-price">Harga</label><input id="product-price" name="price" type="number" min="1" required></div><div class="field"><label for="product-category">Kategori</label><select id="product-category" name="category" required><?php foreach ($categories as $cat): ?><option value="<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></option><?php endforeach; ?></select></div><div class="field"><label for="product-image-upload">Upload Gambar</label><input id="product-image-upload" name="image_upload" type="file" accept="image/*"></div><div class="field"><label for="product-image">Atau masukkan path gambar</label><input id="product-image" name="image" type="text" placeholder="assets/images/bouquets/filename.jpg"></div><button class="wide-button primary" type="submit">Simpan Produk</button></form></div></section>
    <?php elseif ($page === 'admin-assets'): ?>
      <?php if (!is_admin_logged_in()): header('Location: ?page=admin-login'); exit; endif; ?>
      <section class="section-admin-assets">
        <h2>Asset Sorter</h2>
        <p>Tarik dan lepas foto di bawah, atau pilih beberapa file sekaligus. File akan disortir berdasarkan nama file ke folder kategori yang sesuai.</p>

        <?php if (!empty($adminError)): ?>
            <div class="alert alert-danger"><?= htmlentities($adminError) ?></div>
        <?php endif; ?>

        <form id="asset-upload-form" action="?page=admin-assets" method="post" enctype="multipart/form-data" class="upload-form">
            <div id="upload-drop-area" class="upload-drop-area">
                <p>Tarik & lepas gambar di sini</p>
                <p>atau</p>
                <button type="button" class="button-secondary" id="browse-files">Pilih Foto</button>
                <input type="file" name="asset_upload[]" id="asset-upload" accept="image/*" multiple hidden>
            </div>
          <div style="display:flex;gap:8px;align-items:center;">
            <button type="submit" class="button-primary">Proses Foto</button>
            <button type="button" id="scan-unsorted" class="button-secondary">Scan Unsorted</button>
          </div>
        </form>

        <?php if (!empty($assetSortResults)): ?>
            <div class="asset-sort-results">
                <h3>Hasil Sortir</h3>
                <ul>
                    <?php foreach ($assetSortResults as $result): ?>
                        <li><?= htmlentities($result['name']) ?> - <?= htmlentities($result['status']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </section>

    <script>
        const dropArea = document.getElementById('upload-drop-area');
        const fileInput = document.getElementById('asset-upload');
        const browseButton = document.getElementById('browse-files');
      const scanBtn = document.getElementById('scan-unsorted');

        browseButton.addEventListener('click', () => fileInput.click());

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, event => {
                event.preventDefault();
                event.stopPropagation();
                dropArea.classList.add('highlight');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, event => {
                event.preventDefault();
                event.stopPropagation();
                dropArea.classList.remove('highlight');
            });
        });

        dropArea.addEventListener('drop', event => {
            const files = Array.from(event.dataTransfer.files);
            if (!files.length) {
                return;
            }
            fileInput.files = new DataTransfer().files;
            const dataTransfer = new DataTransfer();
            files.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
            dropArea.querySelector('p').textContent = files.length + ' file siap diunggah.';
        });

        scanBtn?.addEventListener('click', () => {
          scanBtn.disabled = true;
          scanBtn.textContent = 'Scanning...';
          fetch('asset_sorter.php')
            .then(r => r.json())
            .then(result => {
              const list = document.createElement('ul');
              if (result.items && result.items.length) {
                result.items.forEach(i => {
                  const li = document.createElement('li');
                  li.textContent = i.name + ' → ' + (i.target || i.status);
                  list.appendChild(li);
                });
              } else {
                list.textContent = 'No files processed.';
              }
              const container = document.querySelector('.asset-sort-results') || document.createElement('div');
              container.className = 'asset-sort-results';
              container.innerHTML = '<h3>Hasil Scan</h3>';
              container.appendChild(list);
              document.getElementById('asset-upload-form').after(container);
            })
            .catch(err => alert('Gagal melakukan scan: ' + err.message))
            .finally(() => { scanBtn.disabled = false; scanBtn.textContent = 'Scan Unsorted'; });
        });
    </script>

<?php elseif ($page === 'admin-products'): ?>
      <?php if (!is_admin_logged_in()): header('Location: ?page=admin-login'); exit; endif; ?>
      <section class="page-title"><h1>Daftar Produk</h1><p>Semua produk yang terdaftar di database.</p></section>
      <section class="content-panel"><div class="panel-body"><table style="width:100%;border-collapse:collapse;"><thead><tr><th style="text-align:left;padding:12px;border-bottom:1px solid rgba(0,0,0,0.08);">Gambar</th><th style="text-align:left;padding:12px;border-bottom:1px solid rgba(0,0,0,0.08);">Nama</th><th style="text-align:left;padding:12px;border-bottom:1px solid rgba(0,0,0,0.08);">Kategori</th><th style="text-align:left;padding:12px;border-bottom:1px solid rgba(0,0,0,0.08);">Harga</th><th style="text-align:left;padding:12px;border-bottom:1px solid rgba(0,0,0,0.08);">Tanggal</th></tr></thead><tbody><?php if (empty($allProducts)): ?><tr><td colspan="5" style="padding:18px;text-align:center;color:var(--muted);">Belum ada produk.</td></tr><?php else: ?><?php foreach ($allProducts as $item): ?><tr><td style="padding:12px;"><img src="<?= htmlspecialchars($item['image'] ?: 'assets/hero.png') ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:80px;height:60px;object-fit:cover;border-radius:12px;"></td><td style="padding:12px;vertical-align:middle;"><?= htmlspecialchars($item['name']) ?></td><td style="padding:12px;vertical-align:middle;"><?= htmlspecialchars($item['category_name']) ?></td><td style="padding:12px;vertical-align:middle;"><?= formatPrice((int)$item['price']) ?></td><td style="padding:12px;vertical-align:middle;"><?= htmlspecialchars($item['created_at']) ?></td></tr><?php endforeach; ?><?php endif; ?></tbody></table></div></section>
    <?php elseif ($category): ?>
      <section class="page-title"><h1><?= htmlspecialchars($category['name']) ?></h1><p>Koleksi <?= htmlspecialchars($category['name']) ?> terpilih dengan desain premium dan harga terbaik.</p></section>
      <section class="catalog-grid">
        <?php foreach ($products as $item): ?>
          <article class="product-card">
            <div class="product-image" style="background-image: url('<?= htmlspecialchars($item['image'] ?: 'assets/hero.png') ?>'); background-size: cover;"></div>
            <div class="card-body">
              <h3><?= htmlspecialchars($item['name']) ?></h3>
              <div class="price-strip"><?= formatPrice((int)$item['price']) ?></div>
            </div>
          </article>
        <?php endforeach; ?>
        <?php if (empty($products)): ?><p>Tidak ada produk di kategori ini.</p><?php endif; ?>
      </section>
    <?php endif; ?>

    <footer class="footer">
      <div><h3>Social</h3><p>Stay current with updates from our social channels.</p><p>Or contact us directly at <a href="tel:+6282122490002">+6282122490002</a> (WA chat/order).</p></div>
      <div><h3>Newsletter</h3><p>Subscribe to get special offers, free giveaways, and once-in-a-lifetime deals.</p><p><a href="#">email@newsletter.com</a></p></div>
      <div><h3>Customer Care</h3><p>Call</p><p><a href="tel:+6281311996099">+6281311996099</a></p><p>Email <a href="mailto:admin@matahariflorist.com">admin@matahariflorist.com</a></p></div>
      <div><h3>Visit Us</h3><p>Jl. Sulaiman No.12A 10, RT.10/RW.3, Sukabumi Utara</p><p>Kec. KB. Jeruk, Kota Jakarta Barat, Jakarta</p><p>Opening Hours Mon - Sunday: 08.00 - 20.00</p></div>
    </footer>
  </main>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var brand = document.querySelector('.brand');
      if (brand) {
        brand.addEventListener('click', function() {
          location.href = '?page=home';
        });
      }

      var form = document.getElementById('create-account-form');
      if (!form) {
        return;
      }

      var message = document.getElementById('create-account-message');
      form.addEventListener('submit', function(event) {
        event.preventDefault();
        var payload = {
          username: document.getElementById('account-username').value,
          email: document.getElementById('account-email').value,
          password: document.getElementById('account-password').value
        };

        fetch('api.php?action=create_account', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        }).then(function(response) {
          return response.json();
        }).then(function(json) {
          if (json.success) {
            message.textContent = json.message;
            message.style.color = '#1f5f32';
          } else {
            message.textContent = json.message;
            message.style.color = '#7d2222';
          }
        }).catch(function() {
          message.textContent = 'Server tidak merespons. Coba lagi nanti.';
          message.style.color = '#7d2222';
        });
      });
    });
  </script>
  <a class="whatsapp-fab" href="https://wa.me/6282122490002" target="_blank" rel="noreferrer" aria-label="WhatsApp">💬</a>
</body>
</html>
