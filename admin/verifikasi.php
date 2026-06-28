<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_order = db_escape($conn, $_GET['id']);
    $aksi = $_GET['aksi'];

    if ($aksi == 'proses') { $status_baru = "Pesanan Diproses"; } 
    elseif ($aksi == 'selesai') { $status_baru = "Selesai"; } 
    elseif ($aksi == 'batal') { $status_baru = "Dibatalkan"; }

    if (isset($status_baru)) {
        db_query($conn, "UPDATE orders SET status_pesanan = '$status_baru' WHERE id_order = '$id_order'");
        header("Location: verifikasi.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pesanan - Admin Matahari Florist</title>
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
                <div class="flex items-center gap-2 bg-black/20 px-3 py-1.5 rounded-full border border-white/10">
                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                    <span class="text-xs font-medium text-white/90 uppercase"><?= htmlspecialchars($_SESSION['admin_username']); ?></span>
                </div>
                <a href="logout.php" class="text-xs bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-bold transition shadow-sm" onclick="return confirm('Akhiri sesi admin?');">Log Out</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-10 px-4">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-brand-brown/10">
            <h2 class="text-2xl font-serif font-bold text-brand-brown border-b border-brand-brown/10 pb-3 mb-6">Manajemen Pesanan Masuk</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-brand-light text-brand-brown text-sm border-b border-brand-brown/20">
                            <th class="p-4">ID Pesanan</th>
                            <th class="p-4">Tanggal & Waktu</th>
                            <th class="p-4">Nama Customer</th>
                            <th class="p-4">Total Tagihan</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-center">Aksi (Update Status)</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <?php
                        $q_order = db_query($conn, "SELECT * FROM orders ORDER BY tanggal_pesan DESC");
                        if (db_num_rows($q_order) > 0) {
                            while ($row = db_fetch_assoc($q_order)) {
                                $badge_color = "bg-gray-100 text-gray-600"; 
                                if ($row['status_pesanan'] == 'Menunggu Pembayaran') $badge_color = "bg-yellow-100 text-yellow-700";
                                elseif ($row['status_pesanan'] == 'Pesanan Diproses') $badge_color = "bg-blue-100 text-blue-700";
                                elseif ($row['status_pesanan'] == 'Selesai') $badge_color = "bg-green-100 text-green-700";
                                elseif ($row['status_pesanan'] == 'Dibatalkan') $badge_color = "bg-red-100 text-red-700";
                        ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="p-4 font-mono font-bold text-gray-800"><?= $row['id_order']; ?></td>
                            <td class="p-4 text-gray-500"><?= date('d M Y, H:i', strtotime($row['tanggal_pesan'])); ?></td>
                            <td class="p-4 font-bold text-brand-brown"><?= $row['nama_pembeli']; ?></td>
                            <td class="p-4 font-bold text-gray-800">Rp <?= number_format($row['total_belanja'], 0, ',', '.'); ?></td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?= $badge_color; ?>">
                                    <?= $row['status_pesanan']; ?>
                                </span>
                            </td>
                            <td class="p-4 text-center space-x-1">
                                <a href="detail_order.php?id=<?= $row['id_order']; ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1.5 rounded text-xs font-bold transition inline-block mb-1 border border-gray-300">Detail</a>
                                
                                <?php if ($row['status_pesanan'] == 'Menunggu Pembayaran'): ?>
                                    <a href="verifikasi.php?aksi=proses&id=<?= $row['id_order']; ?>" onclick="return confirm('Tandai pesanan DIPROSES (Sudah Lunas)?');" class="bg-brand-brown hover:bg-yellow-600 text-white px-3 py-1.5 rounded text-xs font-bold transition inline-block mb-1">Lunas</a>
                                    <a href="verifikasi.php?aksi=batal&id=<?= $row['id_order']; ?>" onclick="return confirm('Yakin membatalkan pesanan ini?');" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-bold transition inline-block mb-1">Batal</a>
                                <?php elseif ($row['status_pesanan'] == 'Pesanan Diproses'): ?>
                                    <a href="verifikasi.php?aksi=selesai&id=<?= $row['id_order']; ?>" onclick="return confirm('Tandai pesanan SELESAI?');" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded text-xs font-bold transition inline-block mb-1">Selesai</a>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs italic inline-block mb-1">Terkunci</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } } else { echo '<tr><td colspan="6" class="p-8 text-center text-gray-500 italic">Belum ada pesanan masuk.</td></tr>'; } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>