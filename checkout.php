<?php
session_start();
require_once 'config/koneksi.php';

// Validasi Keamanan Lapis Pertama: Jika keranjang kosong, blokir akses ke halaman ini
if (empty($_SESSION['cart'])) {
    echo "<script>alert('Keranjang belanja Anda masih kosong. Silakan pilih produk terlebih dahulu.'); window.location='katalog.php';</script>";
    exit;
}

include 'layout/header.php';
?>

<section class="py-16 bg-white min-h-screen">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-serif font-bold text-brand-brown mb-8 border-b pb-4">Checkout Pesanan</h2>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Kolom Form Data Pembeli -->
            <div class="lg:w-2/3 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-xl font-bold text-gray-800 mb-6">Informasi Pengiriman</h3>
                
                <!-- Form akan mengirim data ke proses_checkout.php -->
                <form action="proses_checkout.php" method="POST">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                            <input type="text" name="nama_pembeli" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-brand-brown focus:ring-1 focus:ring-brand-brown" placeholder="Contoh: Budi Santoso">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor WhatsApp *</label>
                            <input type="number" name="no_wa" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-brand-brown focus:ring-1 focus:ring-brand-brown" placeholder="Contoh: 081234567890">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Lengkap Pengiriman *</label>
                        <textarea name="alamat" required rows="3" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-brand-brown focus:ring-1 focus:ring-brand-brown" placeholder="Sertakan nama jalan, RT/RW, kelurahan, kecamatan, dan patokan rumah."></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Pengiriman *</label>
                            <input type="date" name="tanggal_kirim" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-brand-brown focus:ring-1 focus:ring-brand-brown">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam Pengiriman (Opsional)</label>
                            <input type="time" name="waktu_kirim" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-brand-brown focus:ring-1 focus:ring-brand-brown">
                        </div>
                    </div>

                    <!-- Blok Metode Pengiriman -->
                    <div class="mb-6 border border-brand-brown/20 p-5 rounded-lg bg-yellow-50/50">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Metode Pengiriman *</label>
                        <select name="metode_pengiriman" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-brand-brown focus:ring-1 focus:ring-brand-brown mb-2 bg-white">
                            <option value="" disabled selected>-- Pilih Metode Pengiriman --</option>
                            <option value="Ambil Sendiri">Ambil Sendiri di Toko (Self Pickup) - Gratis</option>
                            <option value="Ojek Online">Via Ojek Online (Pesan Sendiri) - Konfirmasi WA</option>
                            <option value="Kurir Toko">Kurir Toko Matahari Florist (Dihitung manual via WA)</option>
                        </select>
                        <div class="text-xs text-gray-600 mt-3 space-y-2 leading-relaxed border-t border-brand-brown/20 pt-3">
                            <p><strong>*Info Ojek Online:</strong> Anda memesan Ojek Online (GoSend/Grab) <strong>sendiri</strong> setelah pembayaran lunas. Wajib mengirimkan *screenshot* profil/pesanan *driver* ke WhatsApp Admin agar bunga tidak salah berikan.</p>
                            <p><strong>*Info Kurir Toko:</strong> Gratis untuk radius < 5 km. Jika lebih dari 5 km, dikenakan biaya Rp 3.000/km. Total biaya kirim akan diinformasikan oleh Admin.</p>
                        </div>
                    </div>

                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Tambahan / Pesan di Kartu Ucapan</label>
                        <textarea name="catatan" rows="3" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-brand-brown focus:ring-1 focus:ring-brand-brown" placeholder="Tuliskan pesan untuk kartu ucapan (jika ada)..."></textarea>
                    </div>

                    <button type="submit" class="w-full bg-brand-brown text-white py-4 rounded font-bold text-lg hover:bg-opacity-90 transition shadow-md">
                        Konfirmasi & Lanjut ke Pembayaran
                    </button>
                </form>
            </div>

            <!-- Kolom Ringkasan Pesanan (Mengambil data dari Session) -->
            <div class="lg:w-1/3">
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 sticky top-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">Ringkasan Pesanan</h3>
                    <div class="space-y-4 mb-6">
                        <?php
                        $total_belanja = 0;
                        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $id => $jumlah) {
                                $id_aman = db_escape($conn, $id);
                                // Memakai SELECT * untuk memastikan data ditarik utuh dari database
                                $query = db_query($conn, "SELECT * FROM products WHERE id = '$id_aman'");
                                
                                if ($query && db_num_rows($query) > 0) {
                                    $p = db_fetch_assoc($query);
                                    $subtotal = $p['harga'] * $jumlah;
                                    $total_belanja += $subtotal;
                        ?>
                                    <div class="flex justify-between items-start text-sm">
                                        <div class="pr-4">
                                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($p['nama_produk']); ?></p>
                                            <p class="text-gray-500"><?php echo $jumlah; ?> x Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></p>
                                        </div>
                                        <p class="font-medium text-gray-800 whitespace-nowrap">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></p>
                                    </div>
                        <?php 
                                }
                            }
                        } else {
                            echo "<p class='text-red-500 text-sm'>Sesi keranjang tidak terbaca oleh sistem.</p>";
                        }
                        ?>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4 flex justify-between items-center">
                        <span class="font-bold text-gray-700 text-lg">Total Tagihan</span>
                        <span class="font-bold text-brand-brown text-xl">Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?></span>
                    </div>
                    <p class="text-xs text-right text-gray-500 mt-2">*Belum termasuk ongkos kirim (jika ada)</p>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include 'layout/footer.php'; ?>