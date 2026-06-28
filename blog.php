<?php
session_start();
require_once 'config/koneksi.php';
include 'layout/header.php';
?>

<main class="max-w-7xl mx-auto py-12 px-4 min-h-screen">
    <!-- Header Halaman -->
    <div class="mb-10 border-b border-brand-brown/10 pb-4">
        <h2 class="text-3xl font-serif font-bold text-gray-800">Blogs</h2>
    </div>

    <!-- Grid Artikel -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-12">
        
        <!-- Artikel 1 -->
        <article>
            <img src="assets/img/blog1.jpg" alt="Bunga Awet" class="w-full h-64 object-cover mb-4">
            <h3 class="text-xl font-bold text-gray-900 mb-2 hover:text-yellow-600 transition cursor-pointer">Cara Agar Bunga Awet Sampai 2 Minggu</h3>
            <p class="text-sm text-gray-600 leading-relaxed">Sayang banget kan kalau bunga segar pemberian si dia cepat layu? Ternyata ada trik rahasia dari florist profesional agar buket bungamu tetap segar merona hingga berminggu-minggu. Yuk, simak langkah mudah perawatannya di sini!</p>
        </article>

        <!-- Artikel 2 -->
        <article>
            <img src="assets/img/blog2.jpg" alt="Mawar Kuning" class="w-full h-64 object-cover mb-4">
            <h3 class="text-xl font-bold text-gray-900 mb-2 hover:text-yellow-600 transition cursor-pointer">Tidak Hanya Merah, Ini Makna Tersembunyi dari Mawar Kuning</h3>
            <p class="text-sm text-gray-600 leading-relaxed">Mawar merah memang klasik untuk menyatakan cinta, tapi tahukah kamu kalau mawar kuning punya pesan rahasia yang tak kalah manis? Sebelum salah kirim, pelajari dulu arti di balik cantiknya kelopak kuning cerah ini.</p>
        </article>

        <!-- Artikel 3 -->
        <article>
            <img src="assets/img/blog3.jpg" alt="Kado Hari Ibu" class="w-full h-64 object-cover mb-4">
            <h3 class="text-xl font-bold text-gray-900 mb-2 hover:text-yellow-600 transition cursor-pointer">Ide Kado Hari Ibu: Apakah Bunga Termasuk Cocok?</h3>
            <p class="text-sm text-gray-600 leading-relaxed">Masih bingung mencari hadiah yang pas untuk Ibunda tercinta? Buket bunga bisa jadi bahasa cinta paling tulus yang menyentuh hati. Temukan rekomendasi jenis bunga terbaik yang melambangkan hangatnya kasih sayang Ibu.</p>
        </article>

        <!-- Artikel 4 -->
        <article>
            <img src="assets/img/blog4.jpg" alt="Buket Pengantin" class="w-full h-64 object-cover mb-4">
            <h3 class="text-xl font-bold text-gray-900 mb-2 hover:text-yellow-600 transition cursor-pointer">Tren Buket Pengantin Kekinian: Perpaduan 2 Warna Makin Diminati</h3>
            <p class="text-sm text-gray-600 leading-relaxed">Tampil memukau di hari bahagia dengan buket bunga yang aesthetic! Bergeser dari gaya satu warna polos, kini tren perpaduan dua warna kontras yang lembut semakin jadi primadona calon pengantin. Intip inspirasinya untuk hari spesialmu!</p>
        </article>

    </div>
</main>

<?php include 'layout/footer.php'; ?>