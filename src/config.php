<?php
// src/config.php

// Konfigurasi OAuth Google untuk autentikasi pengguna
define('GOOGLE_CLIENT_ID', '973105922331-m2d9r46oc4lppi2u5o8uuam0h31471tt.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost:3000/public/callback.php');

/**
 * Fungsi untuk membuat koneksi ke database MySQL
 *
 * @return mysqli Objek koneksi MySQL
 */
function koneksi(): mysqli
{
  // Konfigurasi koneksi database
  $host  = '127.0.0.1';     // Host lokal
  $users = 'root';          // Username default
  $pass  = '';              // Password kosong (default XAMPP)
  $db    = 'Distributor';   // Nama database sesuai SQL dump

  // Aktifkan mode laporan error untuk debugging
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

  try {
    // Buat koneksi dan set karakter encoding
    $conn = mysqli_connect($host, $users, $pass, $db);
    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
  } catch (mysqli_sql_exception $e) {
    // Jika gagal, log error ke file dan tampilkan pesan umum
    error_log('Database connection error: ' . $e->getMessage());
    exit('Terjadi masalah pada koneksi database. Hubungi admin.');
  }
}
