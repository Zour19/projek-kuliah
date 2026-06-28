<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$pesan_sukses = "";
$pesan_error = "";

if (isset($_POST['simpan_produk'])) {
    $nama_produk = db_escape($conn, $_POST['nama_produk']);
    $kategori = db_escape($conn, $_POST['kategori']);
    $harga = preg_replace("/[^0-9]/", "", $_POST['harga']); 
    $deskripsi = db_escape($conn, $_POST['deskripsi']);

    $nama_file = $_FILES['foto_produk']['name'];
    $ukuran_file = $_FILES['foto_produk']['size'];
    $tmp_file = $_FILES['foto_produk']['tmp_name'];
    
    $ext_diizinkan = array('png', 'jpg', 'jpeg', 'webp');
    $x = explode('.', $nama_file);
    $ekstensi = strtolower(end($x));

    if (in_array($ekstensi, $ext_diizinkan) === true) {
        if ($ukuran_file < 2048000) { 
            $foto_baru = time() . '_' . $nama_file;
            $path = '../assets/img/' . $foto_baru;

            if (move_uploaded_file($tmp_file, $path)) {
                $query = "INSERT INTO products (kategori, nama_produk, deskripsi, harga, gambar) 
                          VALUES ('$kategori', '$nama_produk', '$deskripsi', '$harga', '$foto_baru')";
                if (db_query($conn, $query)) {
                    $pesan_sukses = "Produk berhasil ditambahkan ke katalog!";
                } else {
                    $pesan_error = "Gagal menyimpan ke database: " . db_error($conn);
                }
            } else {
                $pesan_error = "Gagal memindahkan file foto ke folder sistem.";
            }
        } else {
            $pesan_error = "Ukuran file terlalu besar! Maksimal 2MB.";
        }
    } else {
        $pesan_error = "Ekstensi file tidak diizinkan! Hanya JPG, PNG, JPEG, WEBP.";
    }
}

if (isset($_GET['hapus'])) {
    $id_hapus = db_escape($conn, $_GET['hapus']);
    $q_foto = db_query($conn, "SELECT gambar FROM products WHERE id = '$id_hapus'");
    if (db_num_rows($q_foto) > 0) {
        $data_foto = db_fetch_assoc($q_foto);
        $path_foto = '../assets/img/' . $data_foto['gambar'];
        
        if (file_exists($path_foto) && !empty($data_foto['gambar'])) {
            unlink($path_foto);
        }
        db_query($conn, "DELETE FROM products WHERE id = '$id_hapus'");
        header("Location: kelola_produk.php"); 
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin Matahari Florist</title>
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
                <a href="kelola_produk.php" class="text-brand-yellow font-bold border-b-2 border-brand-yellow pb-1 text-sm tracking-wide">Katalog</a>
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

    <div class="max-w-7xl mx-auto py-10 px-4 grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-brand-brown/10 sticky top-24">
                <h2 class="text-xl font-serif font-bold text-brand-brown border-b border-brand-brown/10 pb-3 mb-4">Tambah Produk Baru</h2>
                
                <?php if($pesan_sukses): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded text-sm mb-4"><?= $pesan_sukses; ?></div>
                <?php endif; ?>
                <?php if($pesan_error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded text-sm mb-4"><?= $pesan_error; ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Nama Produk</label>
                        <input type="text" name="nama_produk" required class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-brand-yellow focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Kategori</label>
                        <select name="kategori" required class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-brand-yellow focus:outline-none">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Bouquets">Bouquets</option>
                            <option value="Bloom Box">Bloom Box</option>
                            <option value="Flowers">Flowers</option>
                            <option value="Standing Flowers">Standing Flowers</option>
                            <option value="Accessories">Accessories</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Harga (Rp)</label>
                        <input type="number" name="harga" required class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-brand-yellow focus:outline-none" placeholder="Contoh: 150000">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-brand-yellow focus:outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Foto Produk</label>
                        <input type="file" name="foto_produk" required accept="image/*" class="w-full border border-gray-300 px-3 py-2 rounded text-sm bg-gray-50 focus:outline-none">
                        <p class="text-xs text-brand-yellow mt-1">Format: JPG, PNG. Maks: 2MB.</p>
                    </div>
                    <button type="submit" name="simpan_produk" class="w-full bg-brand-brown hover:bg-yellow-600 text-white font-bold py-3 rounded transition mt-4 tracking-wider">
                        UPLOAD PRODUK
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-brand-brown/10">
                <h2 class="text-xl font-serif font-bold text-brand-brown border-b border-brand-brown/10 pb-3 mb-4">Daftar Katalog Produk</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-brand-light text-brand-brown text-sm border-b border-brand-brown/20">
                                <th class="p-3">Foto</th>
                                <th class="p-3">Detail Produk</th>
                                <th class="p-3">Kategori</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php
                            $q_produk = db_query($conn, "SELECT * FROM products ORDER BY id DESC");
                            if (db_num_rows($q_produk) > 0) {
                                while ($row = db_fetch_assoc($q_produk)) {
                            ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                <td class="p-3">
                                    <img src="../<?php echo htmlspecialchars(image_path($row['gambar'] ?? '')); ?>" alt="Foto" class="w-16 h-16 object-cover rounded shadow-sm border border-gray-200">
                                </td>
                                <td class="p-3">
                                    <p class="font-bold text-gray-800 text-base"><?= $row['nama_produk']; ?></p>
                                    <p class="text-brand-yellow font-bold">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></p>
                                </td>
                                <td class="p-3">
                                    <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold"><?= $row['kategori']; ?></span>
                                </td>
                                <td class="p-3 text-right">
                                    <a href="kelola_produk.php?hapus=<?= $row['id']; ?>" 
                                       onclick="return confirm('Hapus produk ini secara permanen?');" 
                                       class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-bold transition">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else { echo '<tr><td colspan="4" class="p-6 text-center text-gray-500 italic">Katalog masih kosong.</td></tr>'; }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>