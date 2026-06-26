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

    if ($page === 'admin-products' && is_admin_logged_in()) {
        $deleteId = (int) ($_POST['delete_id'] ?? 0);
        if ($deleteId > 0) {
            if (delete_product($deleteId)) {
                $adminSuccess = 'Produk berhasil dihapus.';
            } else {
                $adminError = 'Gagal menghapus produk.';
            }
        }
        $allProducts = get_all_products();
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
<?php require_once __DIR__ . '/includes/templates/header.php'; ?>
<?php require_once __DIR__ . '/includes/templates/pages.php'; ?>
<?php require_once __DIR__ . '/includes/templates/footer.php'; ?>
