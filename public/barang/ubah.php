<?php
require_once __DIR__ . '/../../src/functions.php';

// Ambil ID barang dari parameter GET dan validasi
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  header('Location: index.php?msg=ID tidak valid');
  exit;
}

// Ambil data barang berdasarkan ID
$barang = queryOne("SELECT * FROM barang WHERE id = ?", [$id]);
if (!$barang) {
  header('Location: index.php?msg=Data tidak ditemukan');
  exit;
}

// Ambil daftar kategori untuk dropdown
$kategori = queryAll("SELECT * FROM kategori ORDER BY nama ASC");

$errors = [];

// Proses form jika metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Ambil dan bersihkan data input
  $sku         = trim($_POST['sku'] ?? '');
  $nama        = trim($_POST['nama'] ?? '');
  $harga_beli  = (int)preg_replace('/[^0-9]/', '', $_POST['harga_beli'] ?? '0');
  $harga_jual  = (int)preg_replace('/[^0-9]/', '', $_POST['harga_jual'] ?? '0');
  $stok        = (int)($_POST['stok'] ?? 0);
  $id_kategori = (int)($_POST['id_kategori'] ?? $barang['kategori_id']);

  // Validasi input
  if ($sku === '' || $nama === '') $errors[] = 'SKU dan Nama wajib diisi.';
  if ($harga_beli < 0 || $harga_jual < 0) $errors[] = 'Harga tidak boleh negatif.';
  if ($stok < 0) $errors[] = 'Stok tidak boleh negatif.';

  // Validasi kategori
  $cekKategori = queryOne("SELECT id FROM kategori WHERE id = ?", [$id_kategori]);
  if (!$cekKategori) {
    $errors[] = 'Kategori tidak valid.';
  }

  // Validasi SKU agar tidak duplikat dengan barang lain
  $cek = queryOne("SELECT id FROM barang WHERE sku = ? AND id <> ?", [$sku, $id]);
  if ($cek) $errors[] = 'SKU sudah digunakan oleh barang lain.';

  // Siapkan folder gambar
  $imgDirFs = dirname(__DIR__) . '/img';
  if (!is_dir($imgDirFs)) mkdir($imgDirFs, 0777, true);

  // Tentukan nama file gambar yang akan digunakan
  $nama_file = $barang['gambar'] ?: 'nophoto.jpg';

  // Proses upload gambar baru jika tersedia
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
        // Hapus gambar lama jika bukan default
        if (!empty($nama_file) && $nama_file !== 'nophoto.jpg') {
          $oldPath = $imgDirFs . '/' . $nama_file;
          if (is_file($oldPath)) @unlink($oldPath);
        }
        $nama_file = $newName;
      } else {
        $errors[] = 'Gagal mengunggah gambar baru.';
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

    // Redirect jika berhasil, atau tampilkan error
    if ($res['ok']) {
      header('Location: index.php?msg=Perubahan tersimpan');
      exit;
    } else {
      $errors[] = 'Gagal menyimpan perubahan: ' . htmlspecialchars($res['error'] ?? 'unknown');
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Ubah Barang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #F7F2E7;
      color: #4A4A4A;
    }

    h1 {
      font-weight: 600;
      color: #CBA135;
    }

    .card {
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .form-label {
      font-weight: 500;
      color: #4A4A4A;
    }

    .form-control,
    .form-select {
      border-radius: 6px;
      border: 1px solid #ccc;
      transition: box-shadow 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
      box-shadow: 0 0 0 0.2rem rgba(203, 161, 53, 0.25);
      border-color: #CBA135;
    }

    .btn-primary {
      background-color: #CBA135;
      border-color: #CBA135;
    }

    .btn-primary:hover {
      background-color: #a8832f;
      border-color: #a8832f;
    }

    .btn-secondary {
      background-color: #7f8c8d;
      border-color: #7f8c8d;
    }

    .btn-secondary:hover {
      background-color: #5e6e73;
      border-color: #5e6e73;
    }

    .alert-danger {
      border-radius: 8px;
      background-color: #ffe5e5;
      color: #c0392b;
      border: 1px solid #f5c6cb;
    }

    img {
      border-radius: 6px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    small.text-muted {
      font-size: 13px;
    }
  </style>
</head>

<body class="container mt-4">
  <h1 class="mb-4">Ubah Barang</h1>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div id="status"></div>

  <form id="formUbahBarang" enctype="multipart/form-data" class="card p-4 shadow-sm">
    <input type="hidden" name="id" value="<?= $barang['id'] ?>">

    <div class="mb-3">
      <label class="form-label">SKU</label>
      <input type="text" name="sku" class="form-control" required value="<?= htmlspecialchars($_POST['sku'] ?? $barang['sku']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Nama</label>
      <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($_POST['nama'] ?? $barang['nama']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Kategori</label>
      <select name="id_kategori" class="form-select" required>
        <option value="">-- Pilih Kategori --</option>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= $k['id'] ?>" <?= $k['id'] == $barang['kategori_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($k['nama']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Harga Beli</label>
      <input type="text" name="harga_beli" id="harga_beli" class="form-control" value="<?= htmlspecialchars($_POST['harga_beli'] ?? number_format((int)$barang['harga_beli'], 0, ',', '.')) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Harga Jual</label>
      <input type="text" name="harga_jual" id="harga_jual" class="form-control" value="<?= htmlspecialchars($_POST['harga_jual'] ?? number_format((int)$barang['harga_jual'], 0, ',', '.')) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Stok</label>
      <input type="number" name="stok" class="form-control" min="0" value="<?= htmlspecialchars($_POST['stok'] ?? (int)$barang['stok']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label d-block">Gambar Saat Ini</label>
      <img id="preview" src="../img/<?= htmlspecialchars($barang['gambar'] ?: 'nophoto.jpg') ?>" width="120" alt="gambar">
    </div>
    <div class="mb-3">
      <label class="form-label">Ganti Gambar</label>
      <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*">
      <small class="text-muted">Biarkan kosong jika tidak ingin mengganti.</small>
    </div>

    <div class="d-flex gap-2">
      <a href="index.php" class="btn btn-secondary">Kembali</a>
      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
  </form>

  <script>
    // Format input harga menjadi format Rupiah saat diketik
    function formatRupiah(angka) {
      let number_string = (angka || '').toString().replace(/[^,\d]/g, ''),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);
      if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
      }
      rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
      return rupiah ? 'Rp ' + rupiah : '';
    }

    // Terapkan format Rupiah ke input harga beli dan jual
    ['harga_beli', 'harga_jual'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.addEventListener('input', e => e.target.value = formatRupiah(e.target.value));
    });

    // Preview gambar yang dipilih sebelum diunggah
    document.getElementById('gambar').addEventListener('change', function(event) {
      const file = event.target.files[0];
      const preview = document.getElementById('preview');
      if (file) {
        const reader = new FileReader();
        reader.onload = e => preview.src = e.target.result;
        reader.readAsDataURL(file);
      }
    });

    // Kirim data form ubah barang menggunakan AJAX
    document.getElementById('formUbahBarang').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      fetch('../ajax/qajax-ubah-barang.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          const statusDiv = document.getElementById('status');
          if (data.success) {
            statusDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
            if (window.opener && !window.opener.closed) {
              window.opener.refreshBarangTable();
            }
          } else {
            statusDiv.innerHTML = '<div class="alert alert-danger">' + (data.errors || []).join('<br>') + '</div>';
          }
        })
        .catch(() => {
          document.getElementById('status').innerHTML = '<div class="alert alert-danger">Gagal terhubung ke server</div>';
        });
    });
  </script>
</body>

</html>