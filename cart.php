<?php
session_start();
require_once 'config/koneksi.php';

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Logika untuk menambah barang dari halaman detail_produk
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $id_produk = (int)$_GET['id'];
    if (isset($_SESSION['cart'][$id_produk])) {
        $_SESSION['cart'][$id_produk] += 1;
    } else {
        $_SESSION['cart'][$id_produk] = 1;
    }
    header("Location: cart.php");
    exit;
}

// Logika untuk mengupdate kuantitas barang di keranjang
if (isset($_POST['update_cart'])) {
    if (isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $id_produk => $jumlah) {
            $jumlah = (int)$jumlah;
            if ($jumlah > 0) {
                $_SESSION['cart'][$id_produk] = $jumlah;
            } else {
                // Hapus jika kuantitas diatur ke 0
                unset($_SESSION['cart'][$id_produk]);
            }
        }
    }
    header("Location: cart.php");
    exit;
}

// Logika untuk menghapus barang dari keranjang secara satuan
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id_produk = (int)$_GET['id'];
    unset($_SESSION['cart'][$id_produk]);
    header("Location: cart.php");
    exit;
}

include 'layout/header.php';
?>

<section class="py-16 bg-white min-h-screen">
    <div class="max-w-5xl mx-auto px-4">
        <h2 class="text-3xl font-serif font-bold text-brand-brown mb-8 border-b pb-4">Keranjang Belanja</h2>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="text-center py-10 border border-gray-200 rounded-lg bg-gray-50">
                <p class="text-gray-500 mb-6">Keranjang belanja Anda masih kosong.</p>
                <a href="katalog.php" class="bg-brand-brown text-white px-8 py-3 rounded font-bold hover:bg-brand-yellow transition">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <form action="cart.php" method="post">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="p-4 font-semibold text-brand-brown">Produk</th>
                                <th class="p-4 font-semibold text-brand-brown text-center w-32">Jumlah</th>
                                <th class="p-4 font-semibold text-brand-brown text-right">Subtotal</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_belanja = 0;
                            foreach ($_SESSION['cart'] as $id => $jumlah):
                                // Pastikan ID yang masuk ke query valid
                                $id_aman = db_escape($conn, $id);
                                $query = db_query($conn, "SELECT * FROM products WHERE id = '$id_aman'");
                                
                                // Jika produk masih ada di database
                                if ($query && db_num_rows($query) > 0) {
                                    $p = db_fetch_assoc($query);
                                    $subtotal = $p['harga'] * $jumlah;
                                    $total_belanja += $subtotal;
                            ?>
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="p-4 flex items-center space-x-4">
                                        <img src="<?php echo htmlspecialchars(image_path($p['gambar'] ?? '')); ?>" alt="<?php echo htmlspecialchars($p['nama_produk']); ?>" class="w-16 h-16 object-cover rounded border">
                                        <span class="font-medium text-gray-800"><?php echo htmlspecialchars($p['nama_produk']); ?></span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <!-- Input dinamis untuk kuantitas -->
                                        <input type="number" name="qty[<?php echo $id; ?>]" value="<?php echo $jumlah; ?>" min="1" class="w-16 p-2 text-center border rounded focus:outline-none focus:border-brand-brown">
                                    </td>
                                    <td class="p-4 text-right text-gray-800 font-medium">
                                        Rp <?php echo number_format($subtotal, 0, ',', '.'); ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <a href="cart.php?action=remove&id=<?php echo $id; ?>" class="text-red-500 hover:text-red-700 text-sm font-bold">Hapus</a>
                                    </td>
                                </tr>
                            <?php 
                                } 
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                    
                    <div class="p-6 bg-gray-50 flex flex-col sm:flex-row justify-between items-center border-t gap-4">
                        <div class="flex gap-4">
                            <!-- Tombol untuk navigasi menambah produk lain -->
                            <a href="katalog.php" class="border border-brand-brown text-brand-brown px-6 py-2 rounded hover:bg-brand-brown hover:text-white transition">Lanjut Belanja</a>
                            <!-- Tombol untuk mengeksekusi perhitungan ulang kuantitas -->
                            <button type="submit" name="update_cart" class="bg-gray-200 text-gray-800 px-6 py-2 rounded hover:bg-gray-300 transition font-medium">Perbarui Keranjang</button>
                        </div>
                        <div class="text-right">
                            <span class="text-lg text-gray-600 mr-4">Total Tagihan:</span>
                            <span class="text-2xl font-bold text-brand-brown">Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <a href="checkout.php" class="bg-brand-brown text-white px-10 py-3 rounded font-bold hover:bg-brand-yellow transition shadow-md">Lanjut ke Checkout</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php include 'layout/footer.php'; ?>