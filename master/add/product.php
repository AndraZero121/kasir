<?php

include '../../private/middleware/app.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $productName = htmlspecialchars(trim($_POST['product_name']));
  $categoryId = htmlspecialchars(trim($_POST['category_id'])); // Mengambil ID kategori dari dropdown
  $price = htmlspecialchars(trim($_POST['price']));
  $stock = htmlspecialchars(trim($_POST['stock']));

  // Validasi input
  if (!$productName || !$categoryId || !is_numeric($price) || !is_numeric($stock) || $price <= 0 || $stock < 0) {
    header("Location: ../master_data.php?error=Semua field harus diisi dengan benar dan harga harus lebih dari 0, stok tidak boleh negatif.");
    exit();
  } else {
    // Menambahkan produk ke database
    try {
      $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, stock) VALUES (?, ?, ?, ?)");
      $stmt->execute([$productName, $categoryId, $price, $stock]);
      header("Location: ../master_data.php?success=Produk berhasil ditambahkan!");
      exit();
    } catch (Exception $e) {
      header("Location: ../master_data.php?error=Gagal menambahkan produk: " . urlencode($e->getMessage()));
      exit();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Produk</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
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

    h1 {
      font-size: 2rem;
      /* Ukuran font untuk judul */
      margin-bottom: 1rem;
      /* Jarak bawah judul */
    }

    label {
      font-weight: 500;
      /* Berat font untuk label */
    }

    input,
    select {
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
  <div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-4">Tambah Produk</h1>
    <form method="POST">
      <div class="mb-4">
        <label for="product_name" class="block text-sm font-medium text-gray-700">Nama Produk</label>
        <input type="text" id="product_name" name="product_name" required />
      </div>
      <div class="mb-4">
        <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori</label>
        <select id="category_id" name="category_id" required>
          <option value="">Pilih Kategori</option>
          <?php
          // Mengambil kategori dari database untuk dropdown
          $stmt = $pdo->query("SELECT id, name FROM categories");
          while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="' . htmlspecialchars($category['id']) . '">' . htmlspecialchars($category['name']) . '</option>';
          }
          ?>
        </select>
      </div>
      <div class="mb-4">
        <label for="price" class="block text-sm font-medium text-gray-700">Harga</label>
        <input type="number" id="price" name="price" required min="0" />
      </div>
      <div class="mb-4">
        <label for="stock" class="block text-sm font-medium text-gray-700">Stok</label>
        <input type="number" id="stock" name="stock" required min="0" />
      </div>
      <button type="submit">Tambah Produk</button>
    </form>
  </div>
</body>

</html>