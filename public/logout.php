<?php
// Mulai sesi untuk mengakses dan menghapus data session
session_start();

// Hapus semua data yang tersimpan dalam session
$_SESSION = [];
session_unset();
session_destroy();

// Hapus cookie session jika digunakan oleh server
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(
    session_name(),     // Nama cookie session
    '',                 // Kosongkan nilai
    time() - 42000,     // Set waktu kadaluarsa ke masa lalu
    $params["path"],    // Path sesuai konfigurasi session
    $params["domain"],  // Domain sesuai konfigurasi session
    $params["secure"],  // Hanya kirim lewat HTTPS jika diaktifkan
    $params["httponly"] // Tidak bisa diakses lewat JavaScript
  );
}

// Arahkan pengguna kembali ke halaman login setelah logout
header('Location: login.php');
exit;
