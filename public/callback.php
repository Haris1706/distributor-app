<?php
// Mulai sesi untuk menyimpan token dan data pengguna
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/functions.php';

// Pastikan konfigurasi OAuth tersedia
require_once __DIR__ . '/../src/config.php';

use Google\Client;
use Google\Service\Oauth2;

// Inisialisasi Google Client dengan kredensial aplikasi
$client = new Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope('email');
$client->addScope('profile');

// Tangani error dari Google OAuth (misalnya login dibatalkan)
if (isset($_GET['error'])) {
  echo 'Login dibatalkan atau gagal: ' . htmlspecialchars($_GET['error']);
  exit;
}

// Step 1: Tukar kode otorisasi menjadi access token
if (isset($_GET['code'])) {
  try {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token['error'])) {
      throw new Exception($token['error_description'] ?? 'Gagal ambil token');
    }
    $_SESSION['token'] = $token;
    $client->setAccessToken($token);
  } catch (Throwable $e) {
    echo 'Gagal autentikasi: ' . htmlspecialchars($e->getMessage());
    exit;
  }
}

// Step 2: Gunakan token dari session jika tersedia
if (isset($_SESSION['token']) && !$client->getAccessToken()) {
  $client->setAccessToken($_SESSION['token']);
}

// Validasi token yang aktif
if (!$client->getAccessToken() || $client->isAccessTokenExpired()) {
  throw new Exception('Token tidak valid atau sudah kadaluarsa.');
}

// Step 3: Jika token tidak valid, arahkan ulang ke Google OAuth
if (!$client->getAccessToken() || $client->isAccessTokenExpired()) {
  unset($_SESSION['token']);
  header('Location: ' . $client->createAuthUrl());
  exit;
}

// Step 4: Ambil data pengguna dari Google
try {
  $service = new Oauth2($client);
  $userData = $service->userinfo->get();

  $name    = $userData->name ?? '';
  $email   = $userData->email ?? '';
  $picture = $userData->picture ?? null;

  if (!$email) {
    throw new Exception('Email tidak tersedia dari Google.');
  }

  // Simpan atau perbarui data pengguna di database
  $existing = queryOne("SELECT id, name, email, picture FROM users WHERE email = ?", [$email]);
  if ($existing) {
    execute(
      "UPDATE users SET name = ?, picture = ? WHERE email = ?",
      [$name ?: $existing['name'], $picture, $email]
    );
  } else {
    execute(
      "INSERT INTO users (name, email, picture) VALUES (?, ?, ?)",
      [$name, $email, $picture]
    );
  }

  // Set session pengguna dan amankan session ID
  session_regenerate_id(true);
  $_SESSION['users'] = [
    'name' => $name ?: $email,
    'email' => $email,
    'picture' => $picture ?: 'default-avatar.png',
    'login_provider' => 'google'
  ];

  // Simpan token untuk penggunaan lanjutan (opsional)
  $_SESSION['token'] = $client->getAccessToken();

  // Redirect ke halaman dashboard setelah login berhasil
  header('Location: barang/index.php');
  exit;
} catch (Throwable $e) {
  echo 'Gagal mengambil data user: ' . htmlspecialchars($e->getMessage());
  exit;
}
