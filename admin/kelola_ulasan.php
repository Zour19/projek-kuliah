<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_review = db_escape($conn, $_GET['id']);
    $aksi = $_GET['aksi'];

    if ($aksi == 'acc') {
        db_query($conn, "UPDATE reviews SET status = 'approved' WHERE id = '$id_review'");
    } elseif ($aksi == 'hapus') {
        db_query($conn, "DELETE FROM reviews WHERE id = '$id_review'");
    }
    header("Location: kelola_ulasan.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Ulasan - Admin Matahari Florist</title>
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
                <a href="verifikasi.php" class="text-white/70 hover:text-white transition text-sm tracking-wide">Pesanan</a>
                <a href="kelola_ulasan.php" class="text-brand-yellow font-bold border-b-2 border-brand-yellow pb-1 text-sm tracking-wide">Ulasan</a>
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
            <h2 class="text-2xl font-serif font-bold text-brand-brown border-b border-brand-brown/10 pb-3 mb-6">Moderasi Ulasan Pelanggan</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-brand-light text-brand-brown text-sm border-b border-brand-brown/20">
                            <th class="p-4">Tanggal</th>
                            <th class="p-4">Nama Customer</th>
                            <th class="p-4">Rating</th>
                            <th class="p-4 w-1/3">Teks Ulasan</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <?php
                        $q_review = db_query($conn, "SELECT * FROM reviews ORDER BY id DESC");
                        if (db_num_rows($q_review) > 0) {
                            while ($row = db_fetch_assoc($q_review)) {
                                $status_badge = $row['status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700';
                        ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="p-4 text-gray-500"><?= date('d M Y', strtotime($row['tanggal'])); ?></td>
                            <td class="p-4 font-bold text-brand-brown"><?= $row['nama_pelanggan']; ?></td>
                            <td class="p-4 text-brand-yellow font-bold text-lg"><?= str_repeat('★', $row['rating']); ?></td>
                            <td class="p-4 italic text-gray-600">"<?= $row['ulasan']; ?>"</td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?= $status_badge; ?>"><?= strtoupper($row['status']); ?></span>
                            </td>
                            <td class="p-4 text-center space-x-2 whitespace-nowrap">
                                <?php if ($row['status'] == 'pending'): ?>
                                    <a href="kelola_ulasan.php?aksi=acc&id=<?= $row['id']; ?>" class="bg-brand-brown hover:bg-yellow-600 text-white px-3 py-1.5 rounded text-xs font-bold transition inline-block">ACC (Tampil)</a>
                                <?php endif; ?>
                                <a href="kelola_ulasan.php?aksi=hapus&id=<?= $row['id']; ?>" onclick="return confirm('Hapus ulasan ini permanen?');" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-bold transition inline-block">Hapus</a>
                            </td>
                        </tr>
                        <?php } } else { echo '<tr><td colspan="6" class="p-8 text-center text-gray-500 italic">Belum ada ulasan yang masuk.</td></tr>'; } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>