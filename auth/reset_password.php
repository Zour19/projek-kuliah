<?php
session_start();
require_once '../config/koneksi.php';

// Jika sudah login, tendang ke halaman utama
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = db_escape($conn, $_POST['email']);
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // 1. Validasi kecocokan password
    if ($password_baru !== $konfirmasi_password) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        // 2. Cek apakah email terdaftar di tabel customers
        $cek_email = db_query($conn, "SELECT id FROM customers WHERE email = '$email'");
        
        if (db_num_rows($cek_email) === 1) {
            // 3. Hash password baru
            $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
            
            // 4. Update password di tabel customers
            $update_query = "UPDATE customers SET password = '$hashed_password' WHERE email = '$email'";
            
            if (db_query($conn, $update_query)) {
                $success = true;
            } else {
                $error = "Terjadi kesalahan sistem: " . db_error($conn);
            }
        } else {
            $error = "Email tidak ditemukan dalam sistem kami!";
        }
    }
}

include '../layout/header.php';
?>

<div class="flex-grow flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md border border-brand-brown/10">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-brand-brown font-serif">Reset Password</h2>
            <p class="text-sm text-gray-500 mt-2">Masukkan email Anda dan password baru.</p>
        </div>

        <?php if($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm text-center">
                Password berhasil direset! <br>
                <a href="login.php" class="font-bold underline">Klik di sini untuk Login</a>
            </div>
        <?php else: ?>
            <?php if($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-brand-brown mb-1">Email Terdaftar</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow">
                </div>
                <div>
                    <label class="block text-sm font-medium text-brand-brown mb-1">Password Baru</label>
                    <input type="password" name="password_baru" required minlength="6" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow">
                </div>
                <div>
                    <label class="block text-sm font-medium text-brand-brown mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="konfirmasi_password" required minlength="6" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow">
                </div>
                
                <button type="submit" class="w-full bg-brand-brown hover:bg-gray-800 text-white font-bold py-2 px-4 rounded transition mt-4">
                    Simpan Password Baru
                </button>
            </form>
        <?php endif; ?>
        
        <p class="text-center text-sm text-gray-600 mt-6">
            Kembali ke <a href="login.php" class="text-brand-yellow hover:underline font-medium">halaman Login</a>
        </p>
    </div>
</div>

</main>
</body>
</html>