<?php
// Mulai sesi dan cek apakah pengguna sudah login
session_start();
if (!isset($_SESSION['users'])) {
  header('Location: ../index.php');
  exit;
}

// Panggil fungsi bantu dan ambil data pengguna dari sesi
require_once __DIR__ . '/../../src/functions.php';
$users = $_SESSION['users'];

// Query untuk mengambil data barang beserta nama kategori
$sql = "
  SELECT b.*, k.nama AS kategori
  FROM barang b
  LEFT JOIN kategori k ON b.kategori_id = k.id
  ORDER BY b.id DESC
";
$barang = queryAll($sql);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <title>Data Barang | PT SINAR JAYA Distributor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #F7F2E7;
      color: #4A4A4A;
      overflow-x: hidden;
      transform: translateX(100%);
      transition: transform 0.5s ease-in-out;
    }

    body.slide-in-right {
      transform: translateX(0);
    }

    header {
      background: linear-gradient(90deg, #CBA135, #D9D0C7);
      color: #fff;
      padding: 24px 0;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .tagline {
      font-size: 14px;
      color: #f0f0f0;
      margin-top: 4px;
    }

    .user-info,
    .table-wrapper {
      background: #ffffff;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      padding: 20px;
      margin-bottom: 20px;
    }

    .search-box {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 20px;
    }

    .btn-add {
      background-color: #CBA135;
      color: white;
      border: none;
      border-radius: 6px;
      padding: 8px 16px;
      transition: background-color 0.3s ease;
      text-decoration: none;
    }

    .btn-add:hover {
      background-color: #a8832f;
    }

    .table-hover tbody tr:hover {
      background-color: #fdf6e3;
    }

    footer {
      font-size: 13px;
      color: #7f8c8d;
      margin-top: 40px;
      padding: 15px 0;
      background: #f8f9fa;
    }

    .table-wrapper img {
      transition: transform 0.3s ease;
      cursor: zoom-in;
      border-radius: 6px;
    }

    .table-wrapper img:hover {
      transform: scale(4);
      z-index: 10;
      position: relative;
    }
  </style>
</head>

<body>
  <header>
    <div class="container d-flex align-items-center gap-3">
      <img src="../assets/img/logo.png" alt="Logo PT Sinar Jaya" width="80" height="80" style="border-radius: 10px; object-fit: cover;">
      <div>
        <h1 class="fw-semibold mb-0">PT SINAR JAYA Distributor</h1>
        <p class="tagline mb-0">Solusi Pengadaan Barang dengan Cepat & Tepat</p>
      </div>
    </div>
  </header>

  <div class="container py-4">
    <div class="user-info">
      <h4>Selamat datang, <?= htmlspecialchars($users['name'] ?? $users['email']) ?></h4>
      <?php if (!empty($users['picture'])): ?>
        <img src="<?= htmlspecialchars($users['picture']) ?>" alt="Foto Profil" width="50" style="border-radius: 50%;">
      <?php endif; ?>
      <p>Email: <?= htmlspecialchars($users['email']) ?></p>
      <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>

    <div class="search-box">
      <input type="text" id="qajax" class="form-control" placeholder="Cari nama atau SKU...">
      <a href="tambah.php" class="btn-add">+ Tambah Barang</a>
    </div>

    <div class="table-wrapper">
      <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>No</th>
            <th>Gambar</th>
            <th>SKU</th>
            <th>Nama</th>
            <th>Kategori</th>
            <th>Stok</th>
            <th>Harga</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="table-body">
          <?php if (empty($barang)): ?>
            <tr>
              <td colspan="8" class="text-center text-danger">Data tidak ditemukan</td>
            </tr>
          <?php else: ?>
            <?php foreach ($barang as $i => $b): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><img src="../img/<?= htmlspecialchars($b['gambar'] ?: 'nophoto.jpg') ?>" width="60"></td>
                <td><?= htmlspecialchars($b['sku']) ?></td>
                <td><?= htmlspecialchars($b['nama']) ?></td>
                <td><?= htmlspecialchars($b['kategori'] ?? '-') ?></td>
                <td><?= (int)$b['stok'] ?></td>
                <td>Rp <?= number_format((int)$b['harga_jual'], 0, ',', '.') ?></td>
                <td>
                  <a href="ubah.php?id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                  <a href="hapus.php?id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-danger">Hapus</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <footer class="text-center">
    &copy; <?= date('Y') ?> PT SINAR JAYA Distributor. All rights reserved.
  </footer>

  <script>
    // Tambahkan animasi slide-in saat halaman dimuat
    window.addEventListener('DOMContentLoaded', () => {
      document.body.classList.add('slide-in-right');
    });

    // Ambil elemen input dan tabel
    const input = document.getElementById('qajax');
    const tableBody = document.getElementById('table-body');
    const originalData = tableBody.innerHTML;

    // Event listener untuk pencarian barang berdasarkan keyword
    input.addEventListener('keyup', function() {
      const keyword = this.value.trim();

      // Jika keyword kurang dari 2 karakter, tampilkan data asli
      if (keyword.length < 2) {
        tableBody.innerHTML = originalData;
        return;
      }

      // Kirim permintaan AJAX ke server untuk pencarian barang
      fetch('../ajax/qajax-barang.php?q=' + encodeURIComponent(keyword))
        .then(res => res.text())
        .then(data => {
          tableBody.innerHTML = data;
        });
    });

    // Fungsi untuk me-refresh tabel barang (opsional)
    function refreshBarangTable() {
      fetch('ajax/qajax-barang.php?q=')
        .then(res => res.text())
        .then(data => {
          document.getElementById('table-body').innerHTML = data;
        });
    }
  </script>
</body>

</html>