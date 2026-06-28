<?php
session_start();
require_once '../config/koneksi.php';

// PROTEKSI KETAT
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// LOGIKA STATISTIK DASHBOARD
$q_produk = db_query($conn, "SELECT COUNT(id) as total FROM products");
$tot_produk = db_fetch_assoc($q_produk)['total'];

$q_pesanan = db_query($conn, "SELECT COUNT(id_order) as total FROM orders");
$tot_pesanan = db_fetch_assoc($q_pesanan)['total'];

$q_verif = db_query($conn, "SELECT COUNT(id_order) as total FROM orders WHERE status_pesanan = 'Menunggu Pembayaran'");
$tot_verif = db_fetch_assoc($q_verif)['total'];

$q_pendapatan = db_query($conn, "SELECT SUM(total_belanja) as total FROM orders WHERE status_pesanan = 'Selesai'");
$tot_pendapatan = db_fetch_assoc($q_pendapatan)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Matahari Florist</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            yellow: "#D9A05B", 
                            brown: "#5C3D2E",  
                            light: "#FDF8F0",  
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    
    <nav class="bg-brand-brown text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-6 overflow-x-auto whitespace-nowrap">
                <h1 class="text-xl font-serif font-bold text-brand-yellow uppercase tracking-widest border-r border-white/20 pr-6">Matahari</h1>
                
                <a href="dashboard.php" class="text-brand-yellow font-bold border-b-2 border-brand-yellow pb-1 text-sm tracking-wide">Overview</a>
                <a href="kelola_produk.php" class="text-white/70 hover:text-white transition text-sm tracking-wide">Katalog</a>
                <a href="verifikasi.php" class="text-white/70 hover:text-white transition text-sm tracking-wide">Pesanan</a>
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
        
        <div class="mb-8">
            <h2 class="text-2xl font-serif font-bold text-brand-brown mb-1">Ringkasan Eksekutif</h2>
            <p class="text-sm text-gray-500">Performa operasional toko Matahari Florist hari ini.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <div class="bg-white p-6 rounded-xl shadow-sm border border-brand-brown/10 hover:shadow-md transition">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Produk</h3>
                    <div class="p-2 bg-brand-light rounded-lg text-brand-brown">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-bold text-brand-brown"><?= $tot_produk; ?></span>
                    <span class="text-sm font-medium text-gray-400">Items aktif</span>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-sm border border-brand-brown/10 hover:shadow-md transition">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Pesanan</h3>
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-bold text-brand-brown"><?= $tot_pesanan; ?></span>
                    <span class="text-sm font-medium text-gray-400">Transaksi</span>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-brand-brown/10 hover:shadow-md transition relative overflow-hidden">
                <?php if($tot_verif > 0): ?>
                    <div class="absolute top-0 right-0 w-2 h-full bg-red-500"></div>
                <?php endif; ?>
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending Verifikasi</h3>
                    <div class="p-2 bg-red-50 rounded-lg text-red-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-bold <?= $tot_verif > 0 ? 'text-red-600' : 'text-brand-brown'; ?>"><?= $tot_verif; ?></span>
                    <span class="text-sm font-medium <?= $tot_verif > 0 ? 'text-red-400' : 'text-gray-400'; ?>">Harus diproses</span>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-brand-brown/10 hover:shadow-md transition">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pendapatan (Selesai)</h3>
                    <div class="p-2 bg-green-50 rounded-lg text-green-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-sm font-bold text-gray-400">Rp</span>
                    <span class="text-2xl font-bold text-green-600"><?= number_format($tot_pendapatan, 0, ',', '.'); ?></span>
                </div>
            </div>

        </div>
    </div>
</body>
</html>