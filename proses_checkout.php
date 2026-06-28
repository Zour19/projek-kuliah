<?php
session_start();
require_once 'config/koneksi.php';

// Validasi Keamanan Lapis Pertama: Pastikan request berasal dari POST form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses ilegal terdeteksi. Silakan kembali ke keranjang.");
}

// Validasi Lapis Kedua: Pastikan keranjang tidak kosong saat proses dieksekusi
if (empty($_SESSION['cart'])) {
    echo "<script>alert('Keranjang kosong. Manipulasi data dicegah.'); window.location='katalog.php';</script>";
    exit;
}

// Tangkap dan sanitasi data input (Mencegah SQL Injection & XSS)
$nama_pembeli      = db_escape($conn, $_POST['nama_pembeli']);
$no_wa             = db_escape($conn, $_POST['no_wa']);
$alamat            = db_escape($conn, $_POST['alamat']);
$tanggal_kirim     = db_escape($conn, $_POST['tanggal_kirim']);
$waktu_kirim       = db_escape($conn, $_POST['waktu_kirim']);
$catatan           = db_escape($conn, $_POST['catatan']);
$metode_pengiriman = db_escape($conn, $_POST['metode_pengiriman']);

// Ekstraksi ID Pesanan unik (Menggunakan kombinasi waktu dan string acak)
$id_order = 'INV-' . date('YmdHis') . '-' . rand(100, 999);
$tanggal_pesan = date('Y-m-d H:i:s');

// Kalkulasi ulang total dari database secara ketat (bukan dari input form yang bisa dimanipulasi)
$total_belanja = 0;
foreach ($_SESSION['cart'] as $id_produk => $jumlah) {
    $id_aman = db_escape($conn, $id_produk);
    $q_harga = db_query($conn, "SELECT harga FROM products WHERE id = '$id_aman'");
    if ($p = db_fetch_assoc($q_harga)) {
        $total_belanja += ($p['harga'] * $jumlah);
    }
}

// Eksekusi Tahap 1: Simpan ke tabel `orders`
$query_order = "INSERT INTO orders (id_order, nama_pembeli, no_wa, alamat, tanggal_kirim, waktu_kirim, catatan, metode_pengiriman, total_belanja, status_pesanan, tanggal_pesan) 
                VALUES ('$id_order', '$nama_pembeli', '$no_wa', '$alamat', '$tanggal_kirim', '$waktu_kirim', '$catatan', '$metode_pengiriman', '$total_belanja', 'Menunggu Pembayaran', '$tanggal_pesan')";

if (db_query($conn, $query_order)) {
    // Eksekusi Tahap 2: Simpan detail produk ke tabel `order_items`
    foreach ($_SESSION['cart'] as $id_produk => $jumlah) {
        $id_aman = db_escape($conn, $id_produk);
        $q_produk = db_query($conn, "SELECT harga FROM products WHERE id = '$id_aman'");
        if ($p = db_fetch_assoc($q_produk)) {
            $harga_satuan = $p['harga'];
            $subtotal = $harga_satuan * $jumlah;
            
            $query_items = "INSERT INTO order_items (id_order, id_produk, jumlah, harga_satuan, subtotal) 
                            VALUES ('$id_order', '$id_aman', '$jumlah', '$harga_satuan', '$subtotal')";
            db_query($conn, $query_items);
        }
    }
    
    // Eksekusi Tahap 3: Hapus sesi keranjang belanja
    unset($_SESSION['cart']);
    
    // Redirect ke halaman pembayaran dengan membawa parameter ID Order
    header("Location: pembayaran.php?id=$id_order");
    exit;

} else {
    // Output error jika query INSERT gagal
    die("Anomali Sistem Database: " . db_error($conn));
}
?>