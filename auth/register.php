<?php
require_once '../config/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = db_escape($conn, $_POST['nama']);
    $email = db_escape($conn, $_POST['email']);
    $password = $_POST['password']; 
    $wa = db_escape($conn, $_POST['no_wa']); 
    
    // Mengecek apakah email sudah dipakai
    $cek_email = db_query($conn, "SELECT id FROM customers WHERE email = '$email'");
    if (db_num_rows($cek_email) > 0) {
        $error = "Email sudah terdaftar! Gunakan email lain.";
    } else {
        // Enkripsi password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Input ke tabel customers
        $query = "INSERT INTO customers (nama_lengkap, email, password, no_wa) 
                  VALUES ('$nama', '$email', '$hashed_password', '$wa')";
        
        if (db_query($conn, $query)) {
            // Alur sempurna: Alert lalu pindah ke login
            echo "<script>
                    alert('Registrasi berhasil! Silakan login dengan akun Anda.');
                    window.location.href = 'login.php';
                  </script>";
            exit();
        } else {
            $error = "Terjadi kesalahan sistem: " . db_error($conn);
        }
    }
}

include '../layout/header.php';
?>

<div class="flex-grow flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md border border-brand-brown/10">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-brand-brown font-serif">Buat Akun Baru</h2>
            <p class="text-sm text-gray-500 mt-2">Daftar untuk mulai memesan bunga</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-brand-brown mb-1">Nama Lengkap</label>
                <input type="text" name="nama" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow">
            </div>
            <div>
                <label class="block text-sm font-medium text-brand-brown mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow">
            </div>
            <div>
                <label class="block text-sm font-medium text-brand-brown mb-1">Nomor WhatsApp</label>
                <input type="text" name="no_wa" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow">
            </div>
            <div>
                <label class="block text-sm font-medium text-brand-brown mb-1">Password</label>
                <input type="password" name="password" required minlength="6" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-brand-yellow focus:ring-1 focus:ring-brand-yellow">
            </div>
            <button type="submit" class="w-full bg-brand-yellow hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition">
                Daftar Sekarang
            </button>
        </form>
        
        <p class="text-center text-sm text-gray-600 mt-6">
            Sudah punya akun? <a href="login.php" class="text-brand-yellow hover:underline font-medium">Login di sini</a>
        </p>
    </div>
</div>

</main>
</body>
</html>