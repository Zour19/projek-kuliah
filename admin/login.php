<?php
session_start();
require_once '../config/koneksi.php';

// Jika admin sudah login, langsung lempar ke dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = db_escape($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = db_query($conn, "SELECT * FROM admin WHERE username = '$username'");
    
    if (db_num_rows($query) === 1) {
        $admin = db_fetch_assoc($query);
        // Verifikasi kecocokan password dengan hash di database
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: dashboard.php");
            exit();
        }
    }
    $error = "Akses Ditolak! Username atau Password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Admin - Matahari Florist</title>
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
<body class="bg-brand-light flex items-center justify-center min-h-screen font-sans">
    
    <div class="bg-white p-10 rounded-xl shadow-2xl w-full max-w-md border-t-4 border-brand-brown relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-24 h-24 bg-brand-yellow rounded-full opacity-20"></div>
        
        <div class="text-center mb-8 relative z-10">
            <h1 class="text-3xl font-serif font-bold text-brand-brown tracking-wide mb-1">Matahari Florist</h1>
            <h2 class="text-xs font-bold text-brand-yellow uppercase tracking-widest">Admin Workspace</h2>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm flex items-center shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6 relative z-10">
            <div>
                <label class="block text-brand-brown text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" required autocomplete="off" class="w-full bg-gray-50 border border-gray-200 text-gray-800 px-4 py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition" placeholder="Masukkan ID Anda">
            </div>
            <div>
                <label class="block text-brand-brown text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" required class="w-full bg-gray-50 border border-gray-200 text-gray-800 px-4 py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition" placeholder="••••••••">
            </div>
            <button type="submit" class="w-full bg-brand-brown hover:bg-yellow-600 text-white font-bold py-3.5 rounded-md shadow-md transition flex justify-center items-center gap-2 tracking-wide mt-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path></svg>
                OTORISASI MASUK
            </button>
        </form>
    </div>

</body>
</html>