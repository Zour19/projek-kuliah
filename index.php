<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

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
    'home', 'shop', 'blogs', 'create-account', 'login', 'reset-password', 'contact-us', 'our-story',
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
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <main class="page">
    <header class="site-header">
      <div class="brand">Matahari Florist</div>
      <div class="header-actions">
        <a class="icon-button" href="?page=create-account" aria-label="Account">👤</a>
        <a class="icon-button" href="?page=cart" aria-label="Cart">🛒<span class="cart-badge" id="cart-count">0</span></a>
      </div>
    </header>
    <nav class="nav">
      <?= navItem('home', 'Home', $page) ?>
      <?= navItem('shop', 'Shop', $page) ?>
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
    <?php elseif ($page === 'shop'): ?>
      <section class="page-title">
        <h1>Shop</h1>
        <p>Skenario belanja ringan: pilih produk, tambahkan ke keranjang, lalu lanjutkan ke checkout.</p>
      </section>
      <section class="catalog-grid shop-grid" id="shop-items">
        <?php foreach (get_all_products() as $item): ?>
          <article class="product-card">
            <div class="product-image" style="background-image: url('<?= htmlspecialchars($item['image'] ?: 'assets/hero.png') ?>'); background-size: cover;"></div>
            <div class="card-body">
              <h3><?= htmlspecialchars($item['name']) ?></h3>
              <div class="price-strip"><?= formatPrice((int)$item['price']) ?></div>
              <button class="wide-button primary add-to-cart" type="button" data-product='<?= json_encode(['id'=>(int)$item['id'],'name'=>$item['name'],'price'=>(int)$item['price']], JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>'>Tambah ke Keranjang</button>
            </div>
          </article>
        <?php endforeach; ?>
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
      <section class="summary-panel">
        <div class="panel-body">
          <table>
            <thead><tr><th>Nama Produk</th><th>Qty</th><th>Harga</th></tr></thead>
            <tbody id="cart-items">
              <tr><td colspan="3" style="text-align:center;color:var(--muted);">Keranjang masih kosong. Tambahkan produk dari halaman Shop.</td></tr>
            </tbody>
            <tfoot>
              <tr class="total-row"><td>Total</td><td></td><td id="cart-total">Rp 0</td></tr>
            </tfoot>
          </table>
          <div class="form-actions" style="margin-top:24px; gap:12px; display:flex; flex-wrap:wrap;">
            <a class="wide-button secondary" href="?page=shop">Kembali Belanja</a>
            <button id="checkout-button" class="wide-button primary" type="button">Checkout</button>
          </div>
          <div id="cart-empty-message" class="content-panel" style="display:none;margin-top:20px;">
            <div class="panel-body"><p>Keranjang kosong. Ayo pilih produk di halaman Shop.</p></div>
          </div>
        </div>
      </section>
    <?php elseif ($page === 'checkout'): ?>
      <section class="page-title"><h1>Proses Pembayaran</h1><p>Isi data pengiriman dan pembayaran untuk menyelesaikan pesanan. Ini hanya prototipe skenario checkout.</p></section>
      <div class="content-panel">
        <div class="panel-body">
          <div id="checkout-summary">
            <h2>Ringkasan Pesanan</h2>
            <table>
              <thead><tr><th>Nama Produk</th><th>Qty</th><th>Harga</th></tr></thead>
              <tbody id="checkout-items">
                <tr><td colspan="3" style="text-align:center;color:var(--muted);">Tidak ada item di keranjang.</td></tr>
              </tbody>
              <tfoot><tr class="total-row"><td>Total</td><td></td><td id="checkout-total">Rp 0</td></tr></tfoot>
            </table>
          </div>
          <h2>Payment details</h2>
          <form id="checkout-form">
            <div class="field"><label for="card-name">Cardholder Name</label><input id="card-name" type="text" placeholder="Nama pada kartu"></div>
            <div class="field"><label for="card-number">Card Number</label><input id="card-number" type="text" placeholder="1234 5678 9123 4567"></div>
            <div class="field"><label for="expiry">Expiry Date</label><input id="expiry" type="text" placeholder="MM/YY"></div>
            <div class="field"><label for="cvc">CVC</label><input id="cvc" type="text" placeholder="CVC"></div>
            <button class="wide-button primary" type="button" id="pay-now-button">Pay Now</button>
          </form>
          <div id="checkout-message" class="admin-message" style="display:none;margin-top:18px;"></div>
        </div>
      </div>
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
        <div id="asset-sort-results" class="asset-sort-results" style="display:none;"></div>
    </section>

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
  <script src="assets/js/main.js"></script>
  <a class="whatsapp-fab" href="https://wa.me/6282122490002" target="_blank" rel="noreferrer" aria-label="WhatsApp">💬</a>
</body>
</html>
