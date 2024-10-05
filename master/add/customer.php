<?php
// Memproses penambahan pelanggan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  include '../../private/middleware/app.php'; // Pastikan untuk menyertakan koneksi database

  // Validasi input
  $name = isset($_POST['customer_name']) ? htmlspecialchars(trim($_POST['customer_name'])) : '';
  $email = isset($_POST['customer_email']) ? htmlspecialchars(trim($_POST['customer_email'])) : '';
  $phone = isset($_POST['customer_phone']) ? htmlspecialchars(trim($_POST['customer_phone'])) : '';
  $address = isset($_POST['customer_address']) ? htmlspecialchars(trim($_POST['customer_address'])) : '';

  // Pastikan semua field terisi
  if (empty($name) || empty($email) || empty($phone) || empty($address)) {
    header("Location: /master/error.php?message=Semua field harus diisi!"); // Redirect ke halaman error
    exit;
  }

  try {
    $stmt = $pdo->prepare("INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $address]);

    header("Location: ../master_data.php"); // Arahkan ke daftar pelanggan setelah berhasil
    exit;
  } catch (Exception $e) {
    header("Location: /master/error.php?message=Gagal menambahkan pelanggan: " . urlencode($e->getMessage())); // Redirect ke halaman error
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Pelanggan</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f9fafb;
      /* Warna latar belakang minimalis */
    }

    .container {
      max-width: 600px;
      /* Lebar maksimum untuk konten */
      margin: auto;
      /* Pusatkan konten */
      padding: 20px;
      /* Padding untuk konten */
      background-color: white;
      /* Warna latar belakang putih untuk form */
      border-radius: 8px;
      /* Sudut membulat */
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      /* Bayangan halus */
    }

    h2 {
      font-size: 1.5rem;
      /* Ukuran font untuk judul */
      margin-bottom: 1rem;
      /* Jarak bawah judul */
    }

    input,
    textarea {
      border: 1px solid #d1d5db;
      /* Border abu-abu */
      border-radius: 4px;
      /* Sudut membulat */
      padding: 0.5rem;
      /* Padding untuk input */
      width: 100%;
      /* Lebar penuh */
      box-sizing: border-box;
      /* Menghitung padding dalam lebar */
    }

    button {
      background-color: #4caf50;
      /* Warna latar belakang tombol */
      color: white;
      /* Warna teks tombol */
      padding: 0.5rem;
      /* Padding untuk tombol */
      border: none;
      /* Tanpa border */
      border-radius: 4px;
      /* Sudut membulat */
      cursor: pointer;
      /* Kursor tangan saat hover */
    }

    button:hover {
      background-color: #45a049;
      /* Warna saat hover */
    }
  </style>
</head>

<body>
  <div class="container mt-6">
    <h2 class="text-2xl font-semibold mb-4">Tambah Pelanggan</h2>
    <form id="addCustomerForm" method="POST">
      <div class="mb-4">
        <label class="block text-sm font-medium">Nama Pelanggan</label>
        <input type="text" name="customer_name" required placeholder="Masukkan nama pelanggan">
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium">Email Pelanggan</label>
        <input type="email" name="customer_email" required placeholder="Masukkan email pelanggan">
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium">Nomor Telepon</label>
        <input type="text" name="customer_phone" required placeholder="Masukkan nomor telepon">
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium">Alamat</label>
        <textarea name="customer_address" required placeholder="Masukkan alamat pelanggan"></textarea>
      </div>
      <button type="submit">Tambah Pelanggan</button>
    </form>
  </div>
</body>

</html>