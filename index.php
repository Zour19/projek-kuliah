<?php 
session_start(); // HARUS ada untuk memanggil session
require_once 'config/koneksi.php';

// Opsional: Jika ingin index hanya bisa diakses user yang sudah login:
// if (!isset($_SESSION['user_id'])) {
//     header("Location: auth/login.php");
//     exit();
// }

include 'layout/header.php'; 
// ... dst

// Cek filter kategori dari URL, default ke 'Bouquets'
$filter_kat = isset($_GET['kat']) ? db_escape($conn, $_GET['kat']) : 'Bouquets';
?>

<!-- HERO SECTION -->
<section class="relative w-full h-[500px] md:h-[600px] flex items-center justify-start px-8 md:px-24">
    <!-- Pastikan nama file benar: hiro_homepage.jpg -->
    <img src="assets/img/hiro_homepage.png" 
         alt="Matahari Florist Hero" 
         class="absolute inset-0 w-full h-full object-cover">
    
    <div class="absolute inset-0 bg-black/20"></div>
    
    <div class="relative z-10 max-w-xl">
        <h2 class="text-5xl md:text-6xl font-serif font-bold text-white mb-6 drop-shadow-lg">
            Matahari Florist
        </h2>
        <p class="text-white text-xl md:text-2xl font-light leading-relaxed drop-shadow-md">
            Temukan koleksi rangkaian bunga segar terbaik untuk melengkapi momen spesial Anda.
        </p>
    </div>
</section>

<!-- NAVIGASI KONTEN -->
<section class="py-12 bg-brand-light">
    <div class="max-w-5xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <a href="blog.php" class="bg-white p-8 rounded-lg shadow-sm border border-brand-brown/10 hover:shadow-md transition text-center hover:bg-brand-brown hover:text-white group">
                <h3 class="text-xl font-bold text-brand-brown group-hover:text-white transition">Blogs</h3>
            </a>
            <a href="contact.php" class="bg-white p-8 rounded-lg shadow-sm border border-brand-brown/10 hover:shadow-md transition text-center hover:bg-brand-brown hover:text-white group">
                <h3 class="text-xl font-bold text-brand-brown group-hover:text-white transition">Contact Us</h3>
            </a>
            <a href="about.php" class="bg-white p-8 rounded-lg shadow-sm border border-brand-brown/10 hover:shadow-md transition text-center hover:bg-brand-brown hover:text-white group">
                <h3 class="text-xl font-bold text-brand-brown group-hover:text-white transition">Our Story</h3>
            </a>
        </div>
    </div>
</section>


<?php include 'layout/footer.php'; ?>