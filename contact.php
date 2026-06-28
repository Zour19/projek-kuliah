<?php
session_start();
require_once 'config/koneksi.php';
include 'layout/header.php';
?>

<main class="max-w-4xl mx-auto py-12 px-4 min-h-screen text-center">
    
    <h2 class="text-3xl font-serif font-bold text-gray-900 mb-8">Contact Us</h2>

    <!-- Gambar Peta (Bisa di-klik, mengarah ke Google Maps) -->
    <div class="mb-10 overflow-hidden rounded shadow-sm border border-gray-200">
        <a href="https://www.google.com/maps/search/?api=1&query=Matahari+Florist+Jl.+Sulaiman+No.12A+Sukabumi+Utara+Jakarta+Barat" target="_blank" title="Buka di Google Maps" class="block hover:opacity-90 transition">
            <img src="assets/img/map_matahari.jpg" alt="Peta Lokasi Matahari Florist" class="w-full h-auto object-cover">
        </a>
    </div>

    <!-- Informasi Detail -->
    <div class="space-y-6">
        <div>
            <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-2">Kunjungi Kami</p>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Matahari Florist</h3>
            <p class="text-sm text-gray-600 leading-relaxed">
                Jl. Sulaiman No.12A 10, RT.10/RW.3, Sukabumi Utara<br>
                Kec. Kebon Jeruk, Kota Jakarta Barat, Jakarta
            </p>
            <p class="text-sm text-gray-600 mt-2">Mon - Sun : 08.00 - 20.00 WIB</p>
        </div>

        <div class="pt-6">
            <!-- Link WhatsApp Aktif -->
            <p class="text-sm text-gray-600 mb-2">
                WA chat/order 
                <a href="https://wa.me/6287783121288" target="_blank" class="font-bold text-brand-brown hover:text-yellow-600 transition underline">
                    +6287783121288
                </a>
            </p>
            
            <!-- Call Only (Statik sesuai request lu, tidak butuh href tel:) -->
            <p class="text-sm text-gray-600">
                Call only +6281311996099
            </p>
        </div>
    </div>

</main>

<?php include 'layout/footer.php'; ?>