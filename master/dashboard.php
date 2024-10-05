<?php
require '../private/middleware/app.php'; // Pastikan file ini memuat koneksi ke database

// Cek status login
if (!isset($_SESSION['admin_name'])) {
  header('Location: login.php'); // Redirect ke halaman login jika belum login
  exit();
}

// Fetch data untuk menampilkan di dashboard
$orders = $pdo->query("SELECT o.id, o.created_at, c.name AS customer_name, p.name AS product_name, op.quantity, o.total_payment, o.status 
                        FROM orders o 
                        JOIN customers c ON o.customer_id = c.id 
                        JOIN order_products op ON o.id = op.order_id 
                        JOIN products p ON op.product_id = p.id")->fetchAll(PDO::FETCH_ASSOC);

// Fungsi untuk mengupdate status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
  $orderId = $_POST['order_id'];
  $status = $_POST['status'];

  // Validasi status sebelum update
  $validStatuses = ['pending', 'in_order', 'completed', 'canceled'];
  if (in_array($status, $validStatuses)) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $orderId]);
  } else {
    echo '<script>alert("Status tidak valid.");</script>';
  }

  // Refresh data setelah update
  $orders = $pdo->query("SELECT o.id, o.created_at, c.name AS customer_name, p.name AS product_name, op.quantity, o.total_payment, o.status 
                          FROM orders o 
                          JOIN customers c ON o.customer_id = c.id 
                          JOIN order_products op ON o.id = op.order_id 
                          JOIN products p ON op.product_id = p.id")->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk menghapus pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order_id'])) {
  $deleteOrderId = $_POST['delete_order_id'];
  $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
  $stmt->execute([$deleteOrderId]);

  // Refresh data setelah delete
  $orders = $pdo->query("SELECT o.id, o.created_at, c.name AS customer_name, p.name AS product_name, op.quantity, o.total_payment, o.status 
                          FROM orders o 
                          JOIN customers c ON o.customer_id = c.id 
                          JOIN order_products op ON o.id = op.order_id 
                          JOIN products p ON op.product_id = p.id")->fetchAll(PDO::FETCH_ASSOC);
}

// Calculate summary statistics
$totalOrders = count($orders);
$totalRevenue = array_sum(array_column($orders, 'total_payment'));
$pendingOrders = count(array_filter($orders, function ($order) {
  return $order['status'] == 'pending';
}));

// Cek jika tidak ada pesanan
if ($totalOrders === 0) {
  $totalRevenue = 0;
  $pendingOrders = 0;
}

// Menampilkan alert hanya setelah login
echo '<script>
  Swal.fire({
    title: "Status Login",
    text: "Anda sudah login sebagai Admin.",
    icon: "success",
    confirmButtonText: "OK"
  });
</script>';
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Pembayaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
  <div class="min-h-screen flex">
    <!-- Sidebar -->
    <div class="bg-gray-800 text-white w-64 py-7 px-2 hidden md:block">
      <div class="text-2xl font-bold mb-10 text-center">POSTech</div>
      <nav>
        <a href="#" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
          <i class="fas fa-home mr-2"></i>Dashboard
        </a>
        <a href="order.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
          <i class="fas fa-plus-circle mr-2"></i>Tambah Pesanan
        </a>
        <a href="master_data.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
          <i class="fas fa-database mr-2"></i>Master Data
        </a>
        <a href="../index.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
          <i class="fas fa-home mr-2"></i>Back To Index
        </a>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Navbar -->
      <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
          <h1 class="text-2xl font-bold text-gray-900">Dashboard Pembayaran</h1>
          <div class="flex items-center">
            <span class="text-gray-500 mr-2"><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <button class="bg-gray-200 text-gray-700 rounded-full h-8 w-8 flex items-center justify-center">
              <i class="fas fa-user"></i>
            </button>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
        <div class="container mx-auto px-6 py-8">
          <!-- Stats Overview -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                  <i class="fas fa-shopping-cart text-white"></i>
                </div>
                <div class="ml-5">
                  <p class="text-gray-500 text-sm">Total Pesanan</p>
                  <p class="text-2xl font-semibold text-gray-700"><?= $totalOrders ?></p>
                </div>
              </div>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                  <i class="fas fa-dollar-sign text-white"></i>
                </div>
                <div class="ml-5">
                  <p class="text-gray-500 text-sm">Total Pendapatan</p>
                  <p class="text-2xl font-semibold text-gray-700">Rp <?= number_format($totalRevenue, 0, ',', '.') ?></p>
                </div>
              </div>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                  <i class="fas fa-clock text-white"></i>
                </div>
                <div class="ml-5">
                  <p class="text-gray-500 text-sm">Pesanan Pending</p>
                  <p class="text-2xl font-semibold text-gray-700"><?= $pendingOrders ?></p>
                </div>
              </div>
            </div>
          </div>

          <!-- Orders Table -->
          <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
              <h2 class="text-xl font-semibold text-gray-800">Daftar Pesanan</h2>
              <a href="order.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-plus mr-2"></i>Tambah Pesanan
              </a>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pembayaran</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <?php if (empty($orders)): ?>
                    <tr>
                      <td colspan="8" class="py-4 px-6 text-sm text-gray-500 text-center">Tidak ada pesanan yang tersedia.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                      <tr class="hover:bg-gray-50">
                        <td class="py-4 px-6 text-sm font-medium text-gray-900"><?= htmlspecialchars($order['id']) ?></td>
                        <td class="py-4 px-6 text-sm text-gray-500"><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td class="py-4 px-6 text-sm text-gray-500"><?= htmlspecialchars($order['product_name']) ?></td>
                        <td class="py-4 px-6 text-sm text-gray-500"><?= htmlspecialchars($order['quantity']) ?></td>
                        <td class="py-4 px-6 text-sm text-gray-500"><?= htmlspecialchars(date('d-m-Y', strtotime($order['created_at']))) ?></td>
                        <td class="py-4 px-6 text-sm text-gray-500">Rp <?= htmlspecialchars(number_format($order['total_payment'], 0, ',', '.')) ?></td>
                        <td class="py-4 px-6 text-sm">
                          <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?= $order['status'] == 'completed' ? 'bg-green-100 text-green-800' : ($order['status'] == 'in_order' ? 'bg-yellow-100 text-yellow-800' : ($order['status'] == 'canceled' ? 'bg-red-100 text-red-800' : 'bg-red-100 text-red-800')) ?>">
                            <?= htmlspecialchars(ucfirst($order['status'])) ?>
                          </span>
                        </td>
                        <td class="py-4 px-6 text-sm font-medium">
                          <form method="POST" class="inline">
                            <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                            <select name="status" onchange="this.form.submit()" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                              <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                              <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Selesai</option>
                              <option value="canceled" <?= $order['status'] == 'canceled' ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                          </form>
                          <form method="POST" class="inline">
                            <input type="hidden" name="delete_order_id" value="<?= htmlspecialchars($order['id']) ?>">
                            <button type="submit" class="text-red-600 hover:text-red-800 ml-2">Hapus</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

</body>

</html>