<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matahari Florist</title>
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
                            clean: "#FAFAFA",  
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-clean text-gray-900 font-sans flex flex-col min-h-screen">

    <header class="bg-brand-clean border-b border-brand-brown/10 pt-6 pb-2 px-4 md:px-8 sticky top-0 z-50">
        <div class="grid grid-cols-3 items-center mb-6 w-full max-w-7xl mx-auto">
            <div class="flex justify-start"></div>
            <div class="flex justify-center text-center">
                <a href="<?= APP_BASE_URL ?: '' ?>/index.php">
                    <h1 class="text-3xl font-serif font-bold text-brand-brown tracking-wide">Matahari Florist</h1>
                </a>
            </div>
            
            <div class="flex justify-end items-center gap-5 text-brand-brown">
                
                <?php 
                // LOGIKA SKEPTIS: Cek apakah session customer_id ada di sistem
                if (isset($_SESSION['customer_id']) && isset($_SESSION['nama_lengkap'])): 
                    // Ekstrak hanya kata pertama dari nama lengkap agar UI header tidak pecah
                    $nama_depan = explode(' ', trim($_SESSION['nama_lengkap']))[0];
                ?>
                    <div class="flex items-center gap-3">
                        <a href="<?= APP_BASE_URL ?: '' ?>/profil.php" class="flex items-center gap-2 hover:text-brand-yellow transition group">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path></svg>
                            <span class="text-sm font-bold capitalize group-hover:underline">Halo, <?= htmlspecialchars($nama_depan); ?></span>
                        </a>
                        <span class="text-gray-300">|</span>
                        <a href="<?= APP_BASE_URL ?: '' ?>/auth/logout.php" class="text-xs text-red-500 hover:text-red-700 font-bold transition" onclick="return confirm('Yakin ingin keluar dari akun?');">Keluar</a>
                    </div>
                <?php else: ?>
                    <a href="<?= APP_BASE_URL ?: '' ?>/auth/login.php" class="hover:text-brand-yellow transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path></svg>
                    </a>
                <?php endif; ?>

                <a href="<?= APP_BASE_URL ?: '' ?>/cart.php" class="hover:text-brand-yellow transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"></path></svg>
                </a>
            </div>
        </div>

        <nav class="flex justify-center items-center gap-6 text-xs font-bold text-brand-brown uppercase tracking-widest overflow-x-auto whitespace-nowrap px-4 py-3 border-t border-brand-brown/5">
            <a href="<?= APP_BASE_URL ?: '' ?>/katalog.php?kat=Bouquets" class="hover:text-brand-yellow transition">Bouquets</a>
            <a href="<?= APP_BASE_URL ?: '' ?>/katalog.php?kat=Bloom%20Box" class="hover:text-brand-yellow transition">Bloom Box</a>
            <a href="<?= APP_BASE_URL ?: '' ?>/katalog.php?kat=Flowers" class="hover:text-brand-yellow transition">Flowers</a>
            <a href="<?= APP_BASE_URL ?: '' ?>/katalog.php?kat=Standing%20Flowers" class="hover:text-brand-yellow transition">Standing Flowers</a>
            <a href="<?= APP_BASE_URL ?: '' ?>/katalog.php?kat=Accessories" class="hover:text-brand-yellow transition">Accessories</a>
        </nav>
    </header>
    
    <main class="flex-grow">