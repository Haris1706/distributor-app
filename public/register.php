<?php
// Mulai sesi untuk menyimpan data pengguna setelah registrasi
session_start();
require_once __DIR__ . '/../src/functions.php';

$error = '';

// Proses form jika metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Ambil dan bersihkan data input dari form
  $name     = trim($_POST['name']);
  $email    = trim($_POST['email']);
  $password = $_POST['password'];

  // Validasi input kosong
  if ($name === '' || $email === '' || $password === '') {
    $error = 'Semua field wajib diisi';
  } else {
    // Cek apakah email sudah terdaftar
    $cek = queryOne("SELECT * FROM users WHERE email = ?", [$email]);
    if ($cek) {
      $error = 'Email sudah terdaftar, silakan login';
    } else {
      // Enkripsi password sebelum disimpan
      $hash = password_hash($password, PASSWORD_DEFAULT);

      // Simpan data pengguna ke database
      execute(
        "INSERT INTO users (name, email, password) VALUES (?, ?, ?)",
        [$name, $email, $hash]
      );

      // Set session pengguna setelah registrasi berhasil
      $_SESSION['users'] = [
        'name'    => $name,
        'email'   => $email,
        'picture' => null
      ];

      // Redirect ke halaman dashboard
      header('Location: barang/index.php');
      exit;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Daftar Akun | PT SINAR JAYA Distributor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: url('https://images.unsplash.com/photo-1521791136064-7986c2920216') no-repeat center center/cover;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow-x: hidden;
      transition: transform 0.6s ease-in-out;
    }

    body::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.55);
      z-index: 0;
    }

    body.slide-out {
      transform: translateX(-100%);
    }

    .container {
      position: relative;
      z-index: 1;
      max-width: 420px;
      width: 100%;
      padding: 35px 30px;
      background: rgba(255, 255, 255, 0.92);
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
      animation: fadeInUp 0.6s ease;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    h1 {
      text-align: center;
      color: #0d6efd;
      margin-bottom: 25px;
      font-size: 24px;
      font-weight: 700;
    }

    input {
      display: block;
      width: 100%;
      padding: 12px 14px;
      margin: 10px 0;
      border: 1px solid #ced4da;
      border-radius: 8px;
      font-size: 14px;
      transition: border-color 0.3s, box-shadow 0.3s;
      box-sizing: border-box;
    }

    input:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
      outline: none;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #0d6efd;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      font-size: 15px;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #084ec1;
    }

    .error {
      color: red;
      margin-bottom: 10px;
      text-align: center;
      font-weight: 500;
    }

    p {
      text-align: center;
      margin-top: 15px;
    }

    p a {
      color: #0d6efd;
      text-decoration: none;
      font-weight: 500;
    }

    p a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>Daftar Akun Baru</h1>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="text" name="name" placeholder="Nama Lengkap" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Daftar</button>
    </form>

    <p>Sudah punya akun?
      <a href="index.php?anim=slidein" id="loginLink">Login di sini</a>
    </p>
  </div>

  <script>
    // Tambahkan animasi keluar sebelum redirect ke halaman login
    document.getElementById('loginLink').addEventListener('click', function(e) {
      e.preventDefault();
      document.body.classList.add('slide-out');
      setTimeout(() => {
        window.location.href = this.href;
      }, 600);
    });
  </script>
</body>

</html>