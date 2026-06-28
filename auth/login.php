<?php
session_start();
require_once '../config/koneksi.php';

// Jika sudah login, tendang ke halaman utama (Skeptis Check: pastikan pakai customer_id)
if (isset($_SESSION['customer_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = db_escape($conn, $_POST['email']);
    $password = $_POST['password'];

    // Query hanya mencari di tabel customers
    $query = db_query($conn, "SELECT * FROM customers WHERE email = '$email'");
    
    if (db_num_rows($query) === 1) {
        $user = db_fetch_assoc($query);
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            
            // SKEPTIS CHECK: Penamaan session HARUS identik dengan panggilan di header.php
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap']; 
            
            // Karena ini file login khusus customer, langsung arahkan ke index
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}

include '../layout/header.php';
?>

<div class="flex-grow flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md border border-brand-brown/10">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-brand-brown font-serif">Selamat Datang</h2>
            <p class="text-sm text-gray-500 mt-2">Login Pelanggan</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-brand-brown mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow">
            </div>
            <div>
                <label class="block text-sm font-medium text-brand-brown mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow">
            </div>
            <div class="flex justify-end">
                <a href="reset_password.php" class="text-xs text-gray-500 hover:text-brand-yellow transition">Lupa Password?</a>
            </div>
            <button type="submit" class="w-full bg-brand-yellow hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition">
                Login
            </button>
        </form>
        
        <p class="text-center text-sm text-gray-600 mt-6">
            Belum punya akun? <a href="register.php" class="text-brand-yellow hover:underline font-medium">Daftar sekarang</a>
        </p>
    </div>
</div>