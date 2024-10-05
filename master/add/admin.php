<?php

include '../../private/middleware/app.php';

$error = ''; // Variable to store error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = htmlspecialchars(trim($_POST['admin_name']));
  $email = filter_var(trim($_POST['admin_email']), FILTER_VALIDATE_EMAIL);
  $password = $_POST['admin_password'];

  if (!$name || !$email || !$password) {
    $error = "Semua field harus diisi dengan benar.";
  } else {
    if (addAdmin($name, $email, $password)) {
      header("Location: ../master_data.php?success=Admin berhasil ditambahkan!");
      exit;
    } else {
      $error = "Gagal menambahkan admin.";
    }
  }
}

function addAdmin($name, $email, $password)
{
  global $pdo;

  // Hash the password for security
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  try {
    // Memastikan email unik sebelum menambah admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
      return false; // Email sudah terdaftar
    }
    $stmt = $pdo->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword]);

    return true; // Success
  } catch (Exception $e) {
    return false; // Failure
  }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f9fafb;
      /* Warna latar belakang minimalis */
    }

    .container {
      max-width: 500px;
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
      font-size: 1.5rem;
      /* Ukuran font untuk judul */
      margin-bottom: 1rem;
      /* Jarak bawah judul */
    }

    label {
      font-weight: 500;
      /* Berat font untuk label */
    }

    input {
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
      width: 100%;
      padding: 0.5rem;
      background-color: #4caf50;
      /* Warna latar belakang tombol */
      color: white;
      /* Warna teks tombol */
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
  <div class="container">
    <h1>Tambah Admin</h1>
    <?php if ($error): ?>
      <div class="mb-4 text-red-600 text-sm"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="">
      <div class="mb-4">
        <label for="admin_name">Nama</label>
        <input type="text" name="admin_name" id="admin_name" required>
      </div>
      <div class="mb-4">
        <label for="admin_email">Email</label>
        <input type="email" name="admin_email" id="admin_email" required>
      </div>
      <div class="mb-4">
        <label for="admin_password">Kata Sandi</label>
        <input type="password" name="admin_password" id="admin_password" required>
      </div>
      <button type="submit">Tambah Admin</button>
    </form>
  </div>
</body>

</html>