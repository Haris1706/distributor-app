<?php
// Mulai sesi untuk mengakses data pengguna
session_start();

// Ambil data pengguna dari session jika tersedia
$users = $_SESSION['users'] ?? null;

// Tentukan apakah perlu redirect ke dashboard
$redirect = false;
if ($users) {
  $redirect = true;
}

// Ambil pesan error dari parameter URL jika ada
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Login | PT SINAR JAYA Distributor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: url('https://images.unsplash.com/photo-1521791136064-7986c2920216') no-repeat center center/cover;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
      transition: transform 0.4s ease-in-out;
    }

    body.slide-out-left {
      transform: translateX(-100%);
      opacity: 0;
    }

    body::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 0;
    }

    .login-box {
      position: relative;
      z-index: 1;
      max-width: 400px;
      width: 100%;
      padding: 30px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
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

    .google-btn {
      background: #fff;
      border: 1px solid #ddd;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      border-radius: 8px;
      padding: 10px;
      text-decoration: none;
      color: #555;
      font-weight: 500;
      transition: background 0.3s;
    }

    .google-btn:hover {
      background: #f8f9fa;
    }

    .google-btn img {
      width: 20px;
      height: 20px;
      border-radius: 50%;
    }
  </style>
</head>

<body>

  <div class="login-box">
    <h2 class="mb-4 text-center text-primary">Login</h2>

    <?php if ($error): ?>
      <p class="alert alert-danger"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php" class="mb-3">
      <div class="mb-3">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
      </div>
      <div class="mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="mb-3 text-center">
      <a href="google-login.php" class="google-btn">
        <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google">
        <span>Login dengan Google</span>
      </a>
    </div>

    <p class="text-center">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
  </div>

  <script>
    // Cek apakah pengguna sudah login dan perlu diarahkan ke dashboard
    const shouldRedirect = <?= json_encode($redirect) ?>;

    if (shouldRedirect) {
      // Tambahkan animasi keluar sebelum redirect
      document.body.classList.add("slide-out-left");
      setTimeout(() => {
        window.location.href = "barang/index.php";
      }, 400);
    }
  </script>

</body>

</html>