<?php
session_start();
require_once 'config/koneksi.php';

// Validasi apakah ada ID order di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Pesanan tidak ditemukan. Silakan kembali ke beranda.");
}

$id_order = db_escape($conn, $_GET['id']);

// Ambil data pesanan dari database
$query_order = db_query($conn, "SELECT * FROM orders WHERE id_order = '$id_order'");
if (db_num_rows($query_order) === 0) {
    die("Data pesanan tidak valid.");
}
$order = db_fetch_assoc($query_order);

// --- LOGIKA DINAMIS BERDASARKAN METODE PENGIRIMAN ---
$metode = $order['metode_pengiriman'];
$subtotal_format = number_format($order['total_belanja'], 0, ',', '.');

// Default variabel
$alert_title = "";
$alert_text = "";
$wa_tambahan = "";

// Cek kata kunci pada metode pengiriman
if (strpos(strtolower($metode), 'ambil sendiri') !== false || strpos(strtolower($metode), 'pickup') !== false) {
    // Logika jika Self Pickup
    $alert_title = "Langkah Terakhir: Konfirmasi Pesanan!";
    $alert_text = "Pesanan Anda tercatat. Silakan hubungi Admin via WhatsApp untuk mendapatkan nomor rekening pembayaran dan mengonfirmasi waktu pengambilan bunga di toko.";
    $wa_tambahan = "Mohon informasikan rekening pembayarannya. Saya akan mengambil pesanan ini langsung di toko.";

} elseif (strpos(strtolower($metode), 'ojek online') !== false) {
    // Logika jika Ojek Online (Pesan Sendiri)
    $alert_title = "Langkah Terakhir: Pembayaran & Konfirmasi Ojol!";
    $alert_text = "Pesanan tercatat. Hubungi Admin via WhatsApp untuk pembayaran produk. <strong>Ingat:</strong> Anda baru boleh memesan GoSend/Grab setelah Admin mengonfirmasi pembayaran Anda lunas.";
    $wa_tambahan = "Mohon informasikan rekening pembayarannya. Nanti saya akan memesan Ojek Online sendiri setelah pembayaran produk dikonfirmasi lunas.";

} else {
    // Logika jika Kurir Toko (Dihitung Manual)
    $alert_title = "Langkah Terakhir: Tunggu Konfirmasi Ongkir!";
    $alert_text = "Pesanan Anda tercatat. Namun, <strong>JANGAN TRANSFER DULU</strong> sebelum Admin kami menghitung total ongkos kirim melalui WhatsApp.";
    $wa_tambahan = "Mohon bantu hitungkan total keseluruhan (termasuk ongkir kurir toko) dan informasikan detail pembayarannya.";
}

// --- SETUP WHATSAPP LINK ---
$wa_admin = "6281311996099"; // Ganti dengan nomor WA Admin lu

$pesan = "Halo *Matahari Florist*,\n\n";
$pesan .= "Saya telah membuat pesanan di website. Berikut detailnya:\n\n";
$pesan .= "*ID Pesanan:* " . $order['id_order'] . "\n";
$pesan .= "*Nama:* " . $order['nama_pembeli'] . "\n";
$pesan .= "*Subtotal Produk:* Rp " . $subtotal_format . "\n";
$pesan .= "*Metode Pengiriman:* " . $order['metode_pengiriman'] . "\n";
if (strpos(strtolower($metode), 'ambil sendiri') === false) {
    $pesan .= "*Alamat Tujuan:* " . $order['alamat'] . "\n\n"; // Alamat disembunyikan di WA jika ambil sendiri
} else {
    $pesan .= "\n";
}
$pesan .= $wa_tambahan . "\n\nTerima kasih.";

$url_wa = "https://api.whatsapp.com/send?phone=" . $wa_admin . "&text=" . urlencode($pesan);

include 'layout/header.php';
?>

<div class="max-w-4xl mx-auto py-12 px-4">
    <div class="bg-white p-8 rounded-lg shadow-md border border-brand-brown/10">
        
        <div class="bg-yellow-50 border-l-4 border-brand-yellow p-4 mb-8">
            <h3 class="text-lg font-bold text-yellow-800"><?= $alert_title; ?></h3>
            <p class="text-sm text-yellow-700 mt-1"><?= $alert_text; ?></p>
        </div>

        <h2 class="text-3xl font-serif font-bold text-brand-brown mb-2 border-b pb-4">Ringkasan Pesanan</h2>
        
        <div class="my-6 space-y-2 text-gray-800">
            <p><strong class="text-brand-brown">ID Pesanan:</strong> <?= $order['id_order']; ?></p>
            <p><strong class="text-brand-brown">Nama Pembeli:</strong> <?= $order['nama_pembeli']; ?></p>
            <p><strong class="text-brand-brown">Tanggal Kirim/Ambil:</strong> <?= $order['tanggal_kirim']; ?> (<?= $order['waktu_kirim']; ?>)</p>
            <?php if (strpos(strtolower($metode), 'ambil sendiri') === false): ?>
                <p><strong class="text-brand-brown">Alamat:</strong> <?= $order['alamat']; ?></p>
            <?php endif; ?>
        </div>

        <div class="bg-brand-light p-6 rounded-md mb-8 border border-brand-brown/20">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-brand-brown/10 pb-4 mb-4">
                <div>
                    <p class="text-sm text-gray-500 uppercase tracking-wide font-bold">Subtotal Produk</p>
                    <h3 class="text-3xl font-bold text-brand-brown">Rp <?= $subtotal_format; ?></h3>
                </div>
                <div class="mt-4 md:mt-0 text-right">
                    <p class="text-sm text-gray-500 uppercase tracking-wide font-bold">Metode Pengiriman</p>
                    <p class="font-bold text-gray-800 bg-white px-3 py-1 rounded inline-block border border-gray-200"><?= $order['metode_pengiriman']; ?></p>
                </div>
            </div>

            <div class="space-y-3 text-xs md:text-sm text-gray-600 bg-white p-4 rounded border border-gray-200">
                <p><strong>*Info Ojek Online (GoSend/Grab):</strong> Anda memesan sendiri setelah pembayaran bunga lunas. Wajib mengirimkan <em>screenshot</em> profil/pesanan <em>driver</em> ke WhatsApp Admin agar bunga tidak salah berikan.</p>
                <p><strong>*Info Kurir Toko Matahari Florist:</strong> Gratis untuk radius < 5 km. Jika lebih dari 5 km, dikenakan biaya Rp 3.000/km. Total biaya kirim akan diinformasikan oleh Admin di WhatsApp.</p>
                <p><strong>*Info Ambil Sendiri (Self Pickup):</strong> Gratis. Bunga dapat diambil langsung di toko sesuai tanggal & waktu yang dipilih.</p>
            </div>
        </div>

        <a href="<?= $url_wa; ?>" target="_blank" class="block w-full text-center bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-4 rounded-lg shadow-md transition text-lg">
            Hubungi Admin di WhatsApp Sekarang
        </a>
    </div>
</div>

<?php include 'layout/footer.php'; ?>