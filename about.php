<?php
session_start();
require_once 'config/koneksi.php';
include 'layout/header.php';
?>

<main class="max-w-5xl mx-auto py-12 px-4 min-h-screen">
    <!-- Header Halaman -->
    <div class="mb-10 border-b border-brand-brown/10 pb-4">
        <h2 class="text-3xl font-serif font-bold text-gray-800">Our Story</h2>
    </div>

    <!-- Section 1 -->
    <section class="mb-16 text-center">
        <h3 class="text-2xl font-bold text-yellow-500 mb-4">Bunga Segar Setiap Hari, Pesan Lebih Mudah</h3>
        <p class="text-sm text-gray-600 leading-relaxed mb-8 text-justify md:text-center px-0 md:px-12">
            Hadir sejak Agustus 2020, Matahari Florist di Jakarta Barat siap menyuplai ratusan tangkai bunga segar setiap harinya untuk melengkapi momen bahagiamu. Jadikan website ini sebagai katalog digital untuk mencari inspirasi rangkaian impianmu, mulai dari aneka bunga potong, buket cantik, bloom box, hingga standing flower. Temukan desain favoritmu dari rumah, lalu pesan dengan mudah dan cepat melalui WhatsApp, Instagram, atau nikmati promo gratis ongkir dengan checkout praktis via Shopee. Tim kami siap melayani pesananmu dengan sepenuh hati. Kami selalu memastikan setiap tangkai bunga yang dikirim telah melewati standar pengecekan kualitas yang ketat agar kesegarannya tetap terjaga. Jangan ragu untuk berkonsultasi langsung dengan admin kami jika kamu memiliki permintaan desain khusus yang sesuai dengan karakter penerimanya. Bersama Matahari Florist, mari jadikan setiap momen perayaan dan ungkapan rasamu jauh lebih bermakna lewat indahnya bahasa bunga.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <img src="assets/img/story1.jpg" alt="Bunga Matahari Florist" class="w-full h-64 object-cover">
            <img src="assets/img/story2.jpg" alt="Bunga Segar" class="w-full h-64 object-cover">
        </div>
    </section>

    <!-- Section 2 -->
    <section class="mb-12 text-center">
        <h3 class="text-2xl font-bold text-yellow-500 mb-4">Kualitas Premium dengan Harga Terbaik</h3>
        <p class="text-sm text-gray-600 leading-relaxed mb-8 text-justify md:text-center px-0 md:px-12">
            Kami percaya bahwa keindahan alam harus bisa diakses oleh siapa saja dengan harga yang bersahabat, mulai dari Rp99.000 saja. Sebagai pusat penyedia bunga potong segar, kami menerapkan sistem kurasi teliti dan efisien guna meminimalisir bunga yang terbuang (waste). Hasilnya, kamu dipastikan selalu mendapatkan kualitas kesegaran maksimal dengan harga yang paling jujur. Silakan berkreasi sebebas mungkin dengan koleksi tangkai segar pilihan kami, atau biarkan tangan terampil kami menyulapnya menjadi rangkaian buket manis khusus untuk orang tersayang. Setiap pesanan akan dikemas dengan perlindungan ekstra agar pesona dan keharuman bunga tetap utuh hingga tiba di tujuan. Entah itu untuk mempercantik sudut ruangan di rumah atau menjadi hadiah kejutan, keindahan bunga kami siap menghadirkan kehangatan. Percayakan segala kebutuhan floristikmu kepada kami, dan biarkan setiap kelopaknya menceritakan ketulusan hatimu.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <img src="assets/img/story3.jpg" alt="Taman Bunga" class="w-full h-64 object-cover">
            <img src="assets/img/story4.jpg" alt="Kebun Bunga" class="w-full h-64 object-cover">
        </div>
    </section>
</main>

<?php include 'layout/footer.php'; ?>