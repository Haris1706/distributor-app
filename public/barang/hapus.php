<?php
require_once __DIR__ . '/../../src/functions.php';

// Ambil ID dari parameter GET dan validasi sebagai bilangan bulat
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Validasi ID, jika tidak valid arahkan kembali ke halaman utama
if (!$id || $id <= 0) {
  header('Location: index.php?msg=' . urlencode('ID tidak valid'));
  exit;
}

// Ambil data gambar dari barang berdasarkan ID
$row = queryOne("SELECT gambar FROM barang WHERE id = ?", [$id]);

// Jika barang tidak ditemukan, arahkan kembali dengan pesan
if (!$row) {
  header('Location: index.php?msg=' . urlencode('Barang tidak ditemukan'));
  exit;
}

// Hapus data barang dari database
$res = execute("DELETE FROM barang WHERE id = ?", [$id]);

// Jika penghapusan berhasil dan gambar bukan default, hapus file gambar dari server
if ($res['ok'] && $row['gambar'] && $row['gambar'] !== 'nophoto.jpg') {
  $imgPath = __DIR__ . '/../img/' . $row['gambar'];
  if (is_file($imgPath)) {
    @unlink($imgPath);
  }
}

// Redirect kembali ke halaman utama dengan pesan hasil penghapusan
$pesan = $res['ok'] ? 'Data berhasil dihapus' : 'Gagal menghapus data';
header('Location: index.php?msg=' . urlencode($pesan));
exit;
