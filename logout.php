<?php
// 1. Jalankan session_start() untuk mengakses session yang sedang aktif
session_start();

// 2. Hapus semua variabel session yang terdaftar
$_SESSION = array();

// 3. Jika ingin benar-benar membersihkan cookie session pada browser (opsional tapi bagus untuk keamanan)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Hancurkan/destroy session di server
session_destroy();

// 5. Alihkan (redirect) kembali ke halaman utama visitor (Landing Page)
header("Location: index.php");
exit();
?>