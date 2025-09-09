<?php
require_once __DIR__ . '/../../src/functions.php';

// Ambil keyword pencarian dari parameter URL
$q = $_GET['q'] ?? '';
$params = ["%$q%", "%$q%"];

// Query data barang + kategori berdasarkan nama atau SKU
$sql = "
  SELECT b.*, k.nama AS kategori
  FROM barang b
  LEFT JOIN kategori k ON b.kategori_id = k.id
  WHERE b.nama LIKE ? OR b.sku LIKE ?
  ORDER BY b.id DESC
";

$barang = queryAll($sql, $params);

// Tampilkan pesan jika tidak ada hasil
if (empty($barang)) {
  echo '<tr><td colspan="8" class="text-center text-muted fst-italic">Barang tidak ditemukan.</td></tr>';
  exit;
}

// Loop dan tampilkan data barang dalam bentuk tabel
foreach ($barang as $i => $b) {
  echo "<tr>
    <td>" . ($i + 1) . "</td>
    <td><img src='../img/" . htmlspecialchars($b['gambar'] ?: 'nophoto.jpg') . "' width='60'></td>
    <td>" . htmlspecialchars($b['sku']) . "</td>
    <td>" . htmlspecialchars($b['nama']) . "</td>
    <td>" . htmlspecialchars($b['kategori'] ?? '-') . "</td>
    <td>" . (int)$b['stok'] . "</td>
    <td>Rp " . number_format((int)$b['harga_jual'], 0, ',', '.') . "</td>
    <td>
      <a href='ubah.php?id=" . (int)$b['id'] . "' class='btn btn-sm btn-warning'>Edit</a>
      <a href='hapus.php?id=" . (int)$b['id'] . "' class='btn btn-sm btn-danger'>Hapus</a>
    </td>
  </tr>";
}
