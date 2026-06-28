<?php 
require_once 'config/koneksi.php';
include 'layout/header.php'; 

// Ambil ID dari URL dengan aman
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = db_query($conn, "SELECT * FROM products WHERE id = $id");
$p = db_fetch_assoc($query);

// Jika produk tidak ditemukan di database
if (!$p) {
    echo "<div class='max-w-7xl mx-auto py-20 text-center text-brand-brown'>Produk tidak ditemukan.</div>";
    include 'layout/footer.php';
    exit;
}
?>

<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-12">
        <!-- Gambar Produk -->
        <div class="rounded-lg overflow-hidden shadow-lg border border-brand-brown/5">
            <img src="<?php echo htmlspecialchars(image_path($p['gambar'] ?? '')); ?>" 
                 alt="<?php echo htmlspecialchars($p['nama_produk']); ?>" 
                 class="w-full h-auto object-cover">
        </div>

        <!-- Detail Produk -->
        <div class="flex flex-col justify-center">
            <span class="text-brand-yellow font-bold uppercase tracking-widest text-sm mb-2">
                <?php echo htmlspecialchars($p['kategori']); ?>
            </span>
            <h2 class="text-4xl font-serif font-bold text-brand-brown mb-4">
                <?php echo htmlspecialchars($p['nama_produk']); ?>
            </h2>
            <p class="text-2xl text-gray-700 mb-6 font-semibold">
                Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?>
            </p>
            <p class="text-gray-600 leading-relaxed mb-8">
                <?php echo nl2br(htmlspecialchars($p['deskripsi'])); ?>
            </p>
            
            <!-- Tombol Tambah ke Keranjang -->
            <form action="cart.php?action=add&id=<?php echo $p['id']; ?>" method="post">
                <button type="submit" class="w-full md:w-auto bg-brand-brown text-white px-10 py-4 rounded font-bold hover:bg-brand-yellow transition duration-300">
                    Tambah ke Keranjang
                </button>
            </form>
        </div>
    </div>
</section>

<?php include 'layout/footer.php'; ?>