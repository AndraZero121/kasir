<?php
// login_process.php
require '../private/middleware/app.php'; // Pastikan file ini memuat koneksi ke database

// Cek apakah admin sudah login
if (isset($_SESSION['admin_name'])) {
  header('Location: ../index.php'); // Jika sudah login, redirect ke dashboard
  exit();
}

$error_message = ''; // Variabel untuk menyimpan pesan kesalahan

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  // Hash password untuk memeriksa dengan yang tersimpan di database
  $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email");
  $stmt->execute(['email' => $email]);
  $admin = $stmt->fetch();

  if ($admin && password_verify($password, $admin['password'])) {
    // Jika login berhasil, simpan informasi admin di session
    $_SESSION['admin_name'] = $admin['name'];
    header('Location: ../index.php'); // Redirect ke dashboard
    exit();
  } else {
    // Jika login gagal, set pesan kesalahan
    $error_message = 'Email atau sandi Anda salah.';
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>

<body class="bg-gray-100">
  <div class="flex items-center justify-center min-h-screen">
    <form method="POST" class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
      <h2 class="text-2xl font-semibold mb-6 text-center">Login Admin</h2>
      <?php if ($error_message): ?>
        <div class="mb-4 text-red-500 text-sm text-center"><?= $error_message ?></div>
      <?php endif; ?>
      <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" id="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
      </div>
      <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" id="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
      </div>
      <div class="flex justify-end">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Masuk</button>
      </div>
    </form>
  </div>
</body>

</html>