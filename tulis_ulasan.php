<?php
session_start();
require_once 'config/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = db_escape($conn, $_POST['nama']);
    $rating = (int) $_POST['rating'];
    $ulasan = db_escape($conn, $_POST['ulasan']);

    $query = "INSERT INTO reviews (nama_pelanggan, rating, ulasan, status) VALUES ('$nama', '$rating', '$ulasan', 'pending')";
    if (db_query($conn, $query)) {
        echo "<script>alert('Terima kasih! Ulasan Anda akan tampil setelah ditinjau Admin.'); window.location='index.php';</script>";
        exit();
    }
}
include 'layout/header.php';
?>
<div class="max-w-xl mx-auto py-12 px-4">
    <div class="bg-white p-8 rounded-lg shadow-md border border-brand-brown/10">
        <h2 class="text-3xl font-serif font-bold text-brand-brown mb-6 text-center">Beri Kami Ulasan</h2>
        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-brand-brown mb-1">Nama Anda</label>
                <input type="text" name="nama" required class="w-full border border-gray-300 px-4 py-2 rounded focus:ring-1 focus:ring-brand-yellow focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-brand-brown mb-1">Rating (1-5 Bintang)</label>
                <select name="rating" required class="w-full border border-gray-300 px-4 py-2 rounded focus:ring-1 focus:ring-brand-yellow focus:outline-none">
                    <option value="5">★★★★★ (Sangat Puas)</option>
                    <option value="4">★★★★☆ (Puas)</option>
                    <option value="3">★★★☆☆ (Cukup)</option>
                    <option value="2">★★☆☆☆ (Kurang)</option>
                    <option value="1">★☆☆☆☆ (Kecewa)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-brand-brown mb-1">Ulasan / Pengalaman Anda</label>
                <textarea name="ulasan" required rows="4" class="w-full border border-gray-300 px-4 py-2 rounded focus:ring-1 focus:ring-brand-yellow focus:outline-none"></textarea>
            </div>
            <button type="submit" class="w-full bg-brand-yellow hover:bg-yellow-600 text-white font-bold py-3 rounded transition">Kirim Ulasan</button>
        </form>
    </div>
</div>
<?php include 'layout/footer.php'; ?>