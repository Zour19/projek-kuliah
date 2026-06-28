<?php 
require_once 'config/koneksi.php';
include 'layout/header.php'; 

// Mengambil kategori dari URL
$kat = isset($_GET['kat']) ? db_escape($conn, $_GET['kat']) : 'Bouquets';
?>

<section class="py-16 bg-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-serif font-bold text-brand-brown mb-10 text-center uppercase tracking-widest">
            <?php echo htmlspecialchars($kat); ?>
        </h2>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php
            // Menggunakan TRIM untuk menghindari spasi tersembunyi
            // Menggunakan BINARY/COLLATE jika ingin sangat ketat, 
            // namun untuk sekarang kita gunakan query standar yang sudah dibersihkan
            $query = db_query($conn, "SELECT * FROM products WHERE TRIM(kategori) = TRIM('$kat')");
            
            if ($query && db_num_rows($query) > 0) {
                while ($p = db_fetch_assoc($query)) {
                    $gambar = image_path($p['gambar'] ?? '');
                    echo "
                    <div class='bg-white p-4 rounded-lg border border-gray-100 shadow-sm hover:shadow-md transition'>
                        <img src='" . htmlspecialchars($gambar) . "' class='h-64 w-full object-cover mb-4 rounded' alt='".htmlspecialchars($p['nama_produk'])."'>
                        <h5 class='font-bold text-brand-brown truncate'>".htmlspecialchars($p['nama_produk'])."</h5>
                        <p class='text-sm text-brand-yellow mb-4'>Rp " . number_format($p['harga'], 0, ',', '.') . "</p>
                        <a href='detail_produk.php?id={$p['id']}' class='block w-full border border-brand-brown text-brand-brown py-2 rounded text-sm text-center hover:bg-brand-brown hover:text-white transition'>Lihat Detail</a>
                    </div>";
                }
            } else {
                echo "<div class='col-span-full text-center'>
                        <p class='text-gray-500 italic mb-4'>Belum ada produk ditemukan untuk kategori: <strong>" . htmlspecialchars($kat) . "</strong></p>
                        <p class='text-sm text-gray-400'>Coba cek database apakah penulisannya sudah benar-benar sama.</p>
                      </div>";
            }
            ?>
        </div>
    </div>
</section>

<?php include 'layout/footer.php'; ?>