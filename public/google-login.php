<?php
// public/google-login.php

// Mulai sesi untuk menyimpan token dan data pengguna
session_start();

// Autoload library dan konfigurasi OAuth
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config.php'; // Pastikan konstanta GOOGLE_CLIENT_ID, dll tersedia

use Google\Client;

// Inisialisasi objek Google Client
$client = new Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);

// Tentukan scope data yang akan diminta dari pengguna
$client->addScope('email');
$client->addScope('profile');
$client->setPrompt('select_account consent');

// Buat URL autentikasi Google berdasarkan konfigurasi
$authUrl = $client->createAuthUrl();

// Redirect pengguna ke halaman login Google
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit;
