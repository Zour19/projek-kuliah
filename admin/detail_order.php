<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: verifikasi.php");
    exit();
}

$id_order = db_escape($conn, $_GET['id']);

// Ambil data utama pesanan
$query_order = db_query($conn, "SELECT * FROM orders WHERE id_order = '$id_order'");
if (db_num_rows($query_order) == 0) {
    echo "Pesanan tidak ditemukan."; exit();
}
$order = db_fetch_assoc($query_order);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Order <?= $id_order; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { yellow: "#D9A05B", brown: "#5C3D2E", light: "#FDF8F0" } } } } }
    </script>
</head>
<body class="bg-gray-50 font-sans">

    <nav class="bg-brand-brown text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-6 overflow-x-auto whitespace-nowrap">
                <h1 class="text-xl font-serif font-bold text-brand-yellow uppercase tracking-widest border-r border-white/20 pr-6">Matahari</h1>
                <a href="dashboard.php" class="text-white/70 hover:text-white transition text-sm tracking-wide">Overview</a>
                <a href="kelola_produk.php" class="text-white/70 hover:text-white transition text-sm tracking-wide">Katalog</a>
                <a href="verifikasi.php" class="text-brand-yellow font-bold border-b-2 border-brand-yellow pb-1 text-sm tracking-wide">Pesanan</a>
                <a href="kelola_ulasan.php" class="text-white/70 hover:text-white transition text-sm tracking-wide">Ulasan</a>
            </div>
            <div class="flex items-center gap-5">
                <a href="verifikasi.php" class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded font-bold transition shadow-sm">Kembali</a>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-10 px-4">
        <div class="bg-white p-8 rounded-xl shadow-sm border border-brand-brown/10">
            
            <div class="flex justify-between items-start border-b border-brand-brown/10 pb-6 mb-6">
                <div>
                    <h2 class="text-2xl font-serif font-bold text-brand-brown">Invoice: <?= $order['id_order']; ?></h2>
                    <p class="text-sm text-gray-500 mt-1"><?= date('d F Y, H:i', strtotime($order['tanggal_pesan'])); ?></p>
                </div>
                <div class="text-right">
                    <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-gray-100 text-gray-700 border border-gray-200">
                        Status: <?= $order['status_pesanan']; ?>
                    </span>
                </div>
            </div>

            <div class="mb-8 p-4 bg-brand-light rounded-lg border border-brand-yellow/30">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Informasi Pembeli</h3>
                <p class="font-bold text-brand-brown text-lg"><?= $order['nama_pembeli']; ?></p>
                <p class="text-sm text-gray-600 mt-1">Harap hubungi nomor WhatsApp pelanggan yang tertera pada notifikasi HP Anda untuk proses pengiriman.</p>
            </div>

            <h3 class="text-lg font-bold text-gray-800 mb-4">Rincian Belanja</h3>
            
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="p-3 text-sm text-gray-600">No</th>
                            <th class="p-3 text-sm text-gray-600 w-full">ID Pesanan Induk</th>
                            <th class="p-3 text-sm text-gray-600 text-right">Total Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <tr class="border-b">
                            <td class="p-3 text-center">1</td>
                            <td class="p-3 font-bold text-gray-800">Total keseluruhan pesanan dari ID <?= $order['id_order']; ?></td>
                            <td class="p-3 text-right font-bold text-brand-brown">Rp <?= number_format($order['total_belanja'], 0, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-brand-brown text-white font-bold">
                            <td colspan="2" class="p-4 text-right">GRAND TOTAL</td>
                            <td class="p-4 text-right text-brand-yellow text-lg">Rp <?= number_format($order['total_belanja'], 0, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</body>
</html>