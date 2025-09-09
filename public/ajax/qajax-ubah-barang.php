<?php
require_once __DIR__ . '/../../src/functions.php';
header('Content-Type: application/json');

// Ambil ID barang dari data POST
$id = (int)($_POST['id'] ?? 0);
$errors = [];

// Cek apakah data barang dengan ID tersebut tersedia
$barang = queryOne("SELECT * FROM barang WHERE id = ?", [$id]);
if (!$barang) {
  echo json_encode(['success' => false, 'errors' => ['Data tidak ditemukan.']]);
  exit;
}

// Ambil dan bersihkan data input dari form
$sku         = trim($_POST['sku'] ?? '');
$nama        = trim($_POST['nama'] ?? '');
$harga_beli  = (int)preg_replace('/[^0-9]/', '', $_POST['harga_beli'] ?? '0');
$harga_jual  = (int)preg_replace('/[^0-9]/', '', $_POST['harga_jual'] ?? '0');
$stok        = (int)($_POST['stok'] ?? 0);
$id_kategori = (int)($_POST['id_kategori'] ?? $barang['kategori_id']);

// Validasi data input
if ($sku === '' || $nama === '') $errors[] = 'SKU dan Nama wajib diisi.';
if ($harga_beli < 0 || $harga_jual < 0) $errors[] = 'Harga tidak boleh negatif.';
if ($stok < 0) $errors[] = 'Stok tidak boleh negatif.';

// Validasi kategori
$cekKategori = queryOne("SELECT id FROM kategori WHERE id = ?", [$id_kategori]);
if (!$cekKategori) $errors[] = 'Kategori tidak valid.';

// Validasi SKU agar tidak duplikat dengan barang lain
$cek = queryOne("SELECT id FROM barang WHERE sku = ? AND id <> ?", [$sku, $id]);
if ($cek) $errors[] = 'SKU sudah digunakan oleh barang lain.';

// Siapkan folder penyimpanan gambar
$imgDirFs = dirname(__DIR__) . '/img';
if (!is_dir($imgDirFs)) mkdir($imgDirFs, 0777, true);

// Tentukan nama file gambar yang akan digunakan
$nama_file = $barang['gambar'] ?: 'nophoto.jpg';

// Proses upload gambar jika ada file yang dikirim
if (!empty($_FILES['gambar']['name'])) {
  $allowed = ['jpg', 'jpeg', 'png'];
  $ext     = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
  $size    = (int)($_FILES['gambar']['size'] ?? 0);

  if (!in_array($ext, $allowed)) {
    $errors[] = 'Ekstensi gambar harus jpg, jpeg, atau png.';
  } elseif ($size > 2 * 1024 * 1024) {
    $errors[] = 'Ukuran gambar maksimal 2MB.';
  } else {
    $newName = uniqid('brg_', true) . '.' . $ext;
    $tujuan  = $imgDirFs . '/' . $newName;

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $tujuan)) {
      // Hapus gambar lama jika bukan gambar default
      if (!empty($nama_file) && $nama_file !== 'nophoto.jpg') {
        $oldPath = $imgDirFs . '/' . $nama_file;
        if (is_file($oldPath)) @unlink($oldPath);
      }
      $nama_file = $newName;
    } else {
      $errors[] = 'Gagal mengunggah gambar.';
    }
  }
}

// Simpan perubahan ke database jika tidak ada error
if (empty($errors)) {
  $res = execute(
    "UPDATE barang 
     SET sku = ?, nama = ?, harga_beli = ?, harga_jual = ?, stok = ?, gambar = ?, kategori_id = ?
     WHERE id = ?",
    [$sku, $nama, $harga_beli, $harga_jual, $stok, $nama_file, $id_kategori, $id]
  );

  if ($res['ok']) {
    echo json_encode(['success' => true, 'message' => 'Perubahan tersimpan.']);
    exit;
  } else {
    $errors[] = 'Gagal menyimpan perubahan.';
  }
}

// Kirim respon error jika validasi gagal atau proses penyimpanan tidak berhasil
echo json_encode(['success' => false, 'errors' => $errors]);
