<?php
session_start();

// 1. Hapus semua variabel sesi
$_SESSION = array();

// 2. Hapus cookie sesi dari browser jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan sesi
session_destroy();

header("Location: login.php");
exit();
?>