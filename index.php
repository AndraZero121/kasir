<?php
session_start(); // Memulai sesi

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_name'])) {
  header('Location: master/login.php'); // Arahkan ke halaman login jika belum login
  exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Kasir Toko Part PC</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <style>
    body {
      background-color: #f9fafb;
      font-family: 'Arial', sans-serif;
      margin: 0;
      padding: 0;
    }

    .menu-container {
      text-align: center;
      margin-top: 50px;
    }

    .menu-button {
      background-color: #4f46e5;
      color: white;
      padding: 12px 24px;
      border-radius: 8px;
      margin: 10px;
      text-decoration: none;
      transition: background-color 0.3s, transform 0.3s;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      font-size: 1.1rem;
    }

    .menu-button:hover {
      background-color: #3b3f9a;
      transform: scale(1.05);
    }

    .feature-container {
      margin-top: 40px;
      padding: 20px;
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
    }

    .feature {
      background-color: #e5e7eb;
      border-radius: 8px;
      padding: 20px;
      margin: 15px 0;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h1,
    h2,
    h3 {
      color: #1f2937;
    }
  </style>
</head>

<body>

  <div class="menu-container animate__animated animate__fadeIn">
    <h1 class="text-4xl font-bold">Selamat Datang, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</h1>
    <h2 class="text-3xl font-semibold">Pilih Menu</h2>
    <br>
    <a href="master/dashboard.php" class="menu-button">Dashboard</a>
    <a href="master/master_data.php" class="menu-button">Master Data</a>
    <a href="master/order.php" class="menu-button">Order</a>
    <a href="master/logout.php" class="menu-button">Logout</a>
  </div>

  <div class="feature-container">
    <h2 class="text-3xl font-semibold">Fitur Tambahan</h2>
    <div class="feature">
      <h3 class="font-bold text-xl">Laporan Penjualan</h3>
      <p>Melihat laporan penjualan harian, mingguan, dan bulanan.</p>
    </div>

    <div class="feature">
      <h3 class="font-bold text-xl">Manajemen Stok</h3>
      <p>Kelola stok produk dengan mudah dan cepat.</p>
    </div>

    <div class="feature">
      <h3 class="font-bold text-xl">Notifikasi Pesanan</h3>
      <p>Dapatkan notifikasi untuk setiap pesanan baru yang masuk.</p>
    </div>
  </div>

</body>

</html>