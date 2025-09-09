<?php
require_once __DIR__ . '/../../src/functions.php';

// Ambil data kategori untuk ditampilkan pada form
$kategori = queryAll("SELECT * FROM kategori ORDER BY nama ASC");

$errors = [];

// Proses form jika metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Ambil dan bersihkan data input dari form
  $sku         = trim($_POST['sku'] ?? '');
  $nama        = trim($_POST['nama'] ?? '');
  $id_kategori = (int)($_POST['id_kategori'] ?? 0);
  $harga_beli  = (int)preg_replace('/[^0-9]/', '', $_POST['harga_beli'] ?? '0');
  $harga_jual  = (int)preg_replace('/[^0-9]/', '', $_POST['harga_jual'] ?? '0');
  $stok        = (int)($_POST['stok'] ?? 0);

  // Validasi input dasar
  if ($sku === '' || $nama === '') $errors[] = 'SKU dan Nama wajib diisi.';
  if ($harga_beli < 0 || $harga_jual < 0) $errors[] = 'Harga tidak boleh negatif.';
  if ($stok < 0) $errors[] = 'Stok tidak boleh negatif.';

  // Validasi SKU agar tidak duplikat
  $cek = queryOne("SELECT id FROM barang WHERE sku = ?", [$sku]);
  if ($cek) $errors[] = 'SKU sudah digunakan. Gunakan SKU lain.';

  // Siapkan folder penyimpanan gambar
  $imgDirFs = dirname(__DIR__) . '/img';
  if (!is_dir($imgDirFs)) {
    mkdir($imgDirFs, 0777, true);
  }

  // Proses upload gambar jika tersedia
  $nama_file = 'nophoto.jpg';
  if (!empty($_FILES['gambar']['name'])) {
    $allowed = ['jpg', 'jpeg', 'png'];
    $ext     = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    $size    = (int)($_FILES['gambar']['size'] ?? 0);

    if (!in_array($ext, $allowed)) {
      $errors[] = 'Ekstensi gambar harus jpg, jpeg, atau png.';
    } elseif ($size > 2 * 1024 * 1024) {
      $errors[] = 'Ukuran gambar maksimal 2MB.';
    } else {
      $nama_file = uniqid('brg_', true) . '.' . $ext;
      $tujuan    = $imgDirFs . '/' . $nama_file;
      if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $tujuan)) {
        $errors[] = 'Gagal mengunggah gambar.';
        $nama_file = 'nophoto.jpg';
      }
    }
  }

  // Validasi kategori jika tidak ada error sebelumnya
  if (empty($errors)) {
    $cekKategori = queryOne("SELECT id FROM kategori WHERE id = ?", [$id_kategori]);
    if (!$cekKategori) {
      $errors[] = 'Kategori tidak valid.';
    }
  }

  // Simpan data barang ke database jika semua validasi lolos
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

    // Redirect ke halaman utama jika berhasil
    if ($res['ok']) {
      header('Location: index.php?msg=Data berhasil ditambahkan');
      exit;
    } else {
      error_log('SQL Error tambah.php: ' . ($res['error'] ?? 'unknown'));
      $errors[] = 'Gagal menyimpan data';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Tambah Barang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #F7F2E7;
      color: #4A4A4A;
      margin: 0;
      padding: 0;
    }

    h1 {
      font-weight: 600;
      color: #CBA135;
    }

    header {
      background: linear-gradient(90deg, #CBA135, #D9D0C7);
      color: #fff;
      padding: 24px 0;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .tagline {
      font-size: 14px;
      color: #f0f0f0;
      margin-top: 4px;
    }

    .user-info,
    .table-wrapper,
    .card {
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

    .btn-add,
    .btn-primary {
      background-color: #CBA135;
      color: white;
      border: none;
      border-radius: 6px;
      padding: 8px 16px;
      transition: background-color 0.3s ease;
      text-decoration: none;
    }

    .btn-add:hover,
    .btn-primary:hover {
      background-color: #a8832f;
    }

    .btn-outline-primary {
      border-color: #CBA135;
      color: #CBA135;
    }

    .btn-outline-primary:hover {
      background-color: #CBA135;
      color: #fff;
    }

    .btn-outline-primary:focus {
      box-shadow: none;
      outline: none;
    }

    .btn-secondary {
      background-color: #7f8c8d;
      border-color: #7f8c8d;
    }

    .btn-secondary:hover {
      background-color: #5e6e73;
      border-color: #5e6e73;
    }

    .table-hover tbody tr:hover {
      background-color: #fdf6e3;
    }

    footer {
      font-size: 13px;
      color: #7f8c8d;
      margin-top: 40px;
      padding: 15px 0;
      background-color: #f8f9fa;
      text-align: center;
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

    .alert-danger {
      border-radius: 8px;
      background-color: #ffe5e5;
      color: #c0392b;
      border: 1px solid #f5c6cb;
    }

    .modal-content {
      border-radius: 10px;
    }

    .modal-title {
      color: #CBA135;
      font-weight: 600;
    }
  </style>
</head>

<body class="container mt-4">
  <h1 class="mb-4">Tambah Barang</h1>

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

  <form id="formTambahBarang" enctype="multipart/form-data" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label class="form-label">SKU</label>
      <input type="text" name="sku" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Nama</label>
      <input type="text" name="nama" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Kategori</label>
      <div class="d-flex gap-2">
        <select name="id_kategori" id="id_kategori" class="form-select" required>
          <option value="">-- Pilih Kategori --</option>
          <?php foreach ($kategori as $k): ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalKategori">
          +Kategori baru
        </button>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Harga Beli</label>
      <input type="text" name="harga_beli" id="harga_beli" class="form-control" placeholder="Rp 0">
    </div>

    <div class="mb-3">
      <label class="form-label">Harga Jual</label>
      <input type="text" name="harga_jual" id="harga_jual" class="form-control" placeholder="Rp 0">
    </div>

    <div class="mb-3">
      <label class="form-label">Stok Awal</label>
      <input type="number" name="stok" class="form-control" min="0" value="0">
    </div>

    <div class="mb-3">
      <label class="form-label">Gambar</label>

      <img id="preview"
        src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d1/Image_not_available.png/800px-Image_not_available.png?20210219185637"
        alt="Preview"
        width="200"
        class="mb-2 d-block"
        style="border-radius:6px; border:1px solid #ccc; object-fit:cover;">

      <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*">
      <small class="text-muted">Maks 2MB. jpg, jpeg, png.</small>
    </div>

    <div class="d-flex gap-2">
      <a href="index.php" class="btn btn-secondary">Kembali</a>
      <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
  </form>

  <script>
    document.getElementById('formTambahBarang').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      fetch('../ajax/qajax-tambah-barang.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          const statusDiv = document.getElementById('status');
          if (data.success) {
            statusDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
            this.reset();

            // 🔄 Refresh tabel di index.php kalau ada iframe atau parent
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

  <div class="modal fade" id="modalKategori" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="formKategori">
          <div class="modal-header">
            <h5 class="modal-title">Tambah Kategori</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="text" name="nama" class="form-control" placeholder="Nama kategori" required>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Fungsi untuk memformat angka menjadi format mata uang Rupiah
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

    // Terapkan format Rupiah secara otomatis saat input harga diubah
    ['harga_beli', 'harga_jual'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.addEventListener('input', e => e.target.value = formatRupiah(e.target.value));
    });

    // Event handler untuk form tambah kategori
    document.getElementById('formKategori').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      // Kirim data kategori ke server menggunakan fetch
      fetch('kategori-tambah.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          console.log(data);
          if (data.id) {
            // Tambahkan kategori baru ke dropdown dan pilih secara otomatis
            const select = document.getElementById('id_kategori');
            const option = document.createElement('option');
            option.value = data.id;
            option.textContent = data.nama;
            option.selected = true;
            select.appendChild(option);

            // Tutup modal dan reset form
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalKategori'));
            modal.hide();
            this.reset();
          } else {
            alert(data.error || 'Gagal menambahkan kategori');
          }
        })
        .catch(() => alert('Gagal terhubung ke server'));
    });

    // Preview gambar yang dipilih sebelum diunggah
    document.getElementById('gambar').addEventListener('change', function(event) {
      const file = event.target.files[0];
      const preview = document.getElementById('preview');

      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result; // tampilkan gambar yang dipilih
        }
        reader.readAsDataURL(file);
      } else {
        preview.src = '../img/nophoto.jpg'; // kembali ke default jika tidak ada gambar dipilih
      }
    });
  </script>

  <!-- Bootstrap JS untuk mendukung komponen seperti modal -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>