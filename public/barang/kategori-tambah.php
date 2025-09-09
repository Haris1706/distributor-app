<?php
require_once __DIR__ . '/../../src/functions.php';
header('Content-Type: application/json; charset=utf-8');

// Ambil dan bersihkan input nama kategori dari form
$nama = trim($_POST['nama'] ?? '');

// Validasi input kosong
if ($nama === '') {
  echo json_encode(['error' => 'Nama kategori tidak boleh kosong']);
  exit;
}

// Validasi panjang karakter maksimum
if (mb_strlen($nama) > 100) {
  echo json_encode(['error' => 'Nama kategori maksimal 100 karakter']);
  exit;
}

// Cek apakah nama kategori sudah ada (tanpa membedakan huruf besar/kecil)
$cek = queryOne("SELECT id FROM kategori WHERE LOWER(nama) = LOWER(?)", [$nama]);
if ($cek) {
  echo json_encode(['error' => 'Kategori sudah ada']);
  exit;
}

// Simpan data kategori ke database
$res = execute("INSERT INTO kategori (nama) VALUES (?)", [$nama]);

// Jika gagal menyimpan, catat error dan kirim respon gagal
if (!$res['ok']) {
  error_log('SQL Error kategori-tambah: ' . ($res['error'] ?? 'unknown'));
  echo json_encode(['error' => 'Gagal menambahkan kategori']);
  exit;
}

// Kirim respon sukses dengan data kategori yang baru ditambahkan
echo json_encode([
  'id' => $res['insert_id'],
  'nama' => $nama
]);
