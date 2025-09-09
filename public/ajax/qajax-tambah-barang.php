<?php
require_once __DIR__ . '/../../src/functions.php';
header('Content-Type: application/json');

$errors = [];

// Ambil data dari form, pastikan gak ada spasi nyelip
$sku         = trim($_POST['sku'] ?? '');
$nama        = trim($_POST['nama'] ?? '');
$id_kategori = (int)($_POST['id_kategori'] ?? 0);

// Bersihin harga dari karakter selain angka (biar aman kalau input pakai titik/koma)
$harga_beli  = (int)preg_replace('/[^0-9]/', '', $_POST['harga_beli'] ?? '0');
$harga_jual  = (int)preg_replace('/[^0-9]/', '', $_POST['harga_jual'] ?? '0');
$stok        = (int)($_POST['stok'] ?? 0);

// Validasi input dasar, biar gak ada data aneh masuk ke database
if ($sku === '' || $nama === '') $errors[] = 'SKU dan Nama wajib diisi.';
if ($harga_beli < 0 || $harga_jual < 0) $errors[] = 'Harga tidak boleh negatif.';
if ($stok < 0) $errors[] = 'Stok tidak boleh negatif.';

// Cek apakah SKU sudah dipakai barang lain
$cek = queryOne("SELECT id FROM barang WHERE sku = ?", [$sku]);
if ($cek) $errors[] = 'SKU sudah digunakan. Gunakan SKU lain.';

// Pastikan folder gambar ada, kalau belum ya bikin dulu
$imgDirFs = dirname(__DIR__) . '/img';
if (!is_dir($imgDirFs)) {
  mkdir($imgDirFs, 0777, true);
}

// Proses upload gambar (kalau user upload), kalau gagal pakai default
$nama_file = 'nophoto.jpg';
if (!empty($_FILES['gambar']['name'])) {
  $allowed = ['jpg', 'jpeg', 'png'];
  $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
  $size = (int)($_FILES['gambar']['size'] ?? 0);

  if (!in_array($ext, $allowed)) {
    $errors[] = 'Ekstensi gambar harus jpg, jpeg, atau png.';
  } elseif ($size > 2 * 1024 * 1024) {
    $errors[] = 'Ukuran gambar maksimal 2MB.';
  } else {
    $nama_file = uniqid('brg_', true) . '.' . $ext;
    $tujuan = $imgDirFs . '/' . $nama_file;
    if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $tujuan)) {
      $errors[] = 'Gagal mengunggah gambar.';
      $nama_file = 'nophoto.jpg';
    }
  }
}

// Cek apakah kategori yang dipilih benar-benar ada
if (empty($errors)) {
  $cekKategori = queryOne("SELECT id FROM kategori WHERE id = ?", [$id_kategori]);
  if (!$cekKategori) {
    $errors[] = 'Kategori tidak valid.';
  }
}

// Kalau semua aman, simpan data ke database
if (empty($errors)) {
  $res = execute(
    "INSERT INTO barang (nama, sku, harga_beli, harga_jual, stok, gambar, kategori_id) 
         VALUES (?, ?, ?, ?, ?, ?, ?)",
    [
      $nama,
      $sku,
      $harga_beli,
      $harga_jual,
      $stok,
      $nama_file,
      $id_kategori
    ]
  );

  // Kirim respon sukses ke frontend
  if ($res['ok']) {
    echo json_encode([
      'success' => true,
      'message' => 'Barang berhasil ditambahkan'
    ]);
    exit;
  } else {
    $errors[] = 'Gagal menyimpan data';
  }
}

// Kalau ada error, kirim semua pesan error ke frontend
echo json_encode([
  'success' => false,
  'errors'  => $errors
]);
