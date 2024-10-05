<?php
// Include database connection
include '../private/middleware/app.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_name'])) {
  header('Location: login.php');
  exit();
}

// Fetch products with category names
$sql_products = "SELECT p.id AS product_id, p.name AS product_name, c.name AS category_name, p.price, p.stock, p.category_id 
                 FROM products p
                 LEFT JOIN categories c ON p.category_id = c.id";
$stmt_products = $pdo->prepare($sql_products);
$stmt_products->execute();
$products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

// Fetch customers
$sql_customers = "SELECT id AS customer_id, name, email, phone, address FROM customers";
$stmt_customers = $pdo->prepare($sql_customers);
$stmt_customers->execute();
$customers = $stmt_customers->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories
$sql_categories = "SELECT id AS category_id, name AS category_name, description FROM categories";
$stmt_categories = $pdo->prepare($sql_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

// Fetch admins
$sql_admins = "SELECT id AS admin_id, email FROM admins";
$stmt_admins = $pdo->prepare($sql_admins);
$stmt_admins->execute();
$admins = $stmt_admins->fetchAll(PDO::FETCH_ASSOC);

// Process edit data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_data'])) {
  $table = filter_input(INPUT_POST, 'table', FILTER_SANITIZE_STRING);
  $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

  try {
    switch ($table) {
      case 'products':
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
        $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $stock = filter_input(INPUT_POST, 'stock', FILTER_SANITIZE_NUMBER_INT);

        $sql_update = "UPDATE products SET name = ?, category_id = ?, price = ?, stock = ? WHERE id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$name, $category_id, $price, $stock, $id]);
        break;

      case 'customers':
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

        $sql_update = "UPDATE customers SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$name, $email, $phone, $address, $id]);
        break;

      case 'categories':
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

        $sql_update = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$name, $description, $id]);
        break;

      case 'admins':
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        if (!empty($password)) {
          $hashed_password = password_hash($password, PASSWORD_DEFAULT);
          $sql_update = "UPDATE admins SET email = ?, password = ? WHERE id = ?";
          $stmt_update = $pdo->prepare($sql_update);
          $stmt_update->execute([$email, $hashed_password, $id]);
        } else {
          $sql_update = "UPDATE admins SET email = ? WHERE id = ?";
          $stmt_update = $pdo->prepare($sql_update);
          $stmt_update->execute([$email, $id]);
        }
        break;
    }

    $_SESSION['success_message'] = "Data berhasil diperbarui.";
    header('Location: master_data.php');
    exit();
  } catch (PDOException $e) {
    $_SESSION['error_message'] = "Gagal memperbarui data: " . $e->getMessage();
    header('Location: master_data.php');
    exit();
  }
}

// Delete data function
function deleteData($table, $id)
{
  global $pdo;
  $sql_delete = "DELETE FROM $table WHERE id = :id";
  $stmt_delete = $pdo->prepare($sql_delete);
  $stmt_delete->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt_delete->execute();

  return $stmt_delete->rowCount() > 0;
}

// Process delete data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_data'])) {
  $table = filter_input(INPUT_POST, 'table', FILTER_SANITIZE_STRING);
  $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

  if (deleteData($table, $id)) {
    $_SESSION['success_message'] = "Data berhasil dihapus.";
  } else {
    $_SESSION['error_message'] = "Gagal menghapus data.";
  }

  header('Location: master_data.php');
  exit();
}

// Process add new data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_data'])) {
  $table = filter_input(INPUT_POST, 'table', FILTER_SANITIZE_STRING);

  try {
    switch ($table) {
      case 'products':
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
        $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $stock = filter_input(INPUT_POST, 'stock', FILTER_SANITIZE_NUMBER_INT);

        $sql_insert = "INSERT INTO products (name, category_id, price, stock) VALUES (?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$name, $category_id, $price, $stock]);
        break;

      case 'customers':
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

        $sql_insert = "INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$name, $email, $phone, $address]);
        break;

      case 'categories':
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

        $sql_insert = "INSERT INTO categories (name, description) VALUES (?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$name, $description]);
        break;

      case 'admins':
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $sql_insert = "INSERT INTO admins (email, password) VALUES (?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$email, $password]);
        break;
    }

    $_SESSION['success_message'] = "Data baru berhasil ditambahkan.";
    header('Location: master_data.php');
    exit();
  } catch (PDOException $e) {
    $_SESSION['error_message'] = "Gagal menambahkan data: " . $e->getMessage();
    header('Location: master_data.php');
    exit();
  }
}

// Search functionality
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($search_query)) {
  $search_query = '%' . $search_query . '%';

  $sql_products .= " WHERE p.name LIKE :search OR c.name LIKE :search";
  $stmt_products = $pdo->prepare($sql_products);
  $stmt_products->bindParam(':search', $search_query, PDO::PARAM_STR);
  $stmt_products->execute();
  $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

  $sql_customers .= " WHERE name LIKE :search OR email LIKE :search OR phone LIKE :search";
  $stmt_customers = $pdo->prepare($sql_customers);
  $stmt_customers->bindParam(':search', $search_query, PDO::PARAM_STR);
  $stmt_customers->execute();
  $customers = $stmt_customers->fetchAll(PDO::FETCH_ASSOC);

  $sql_categories .= " WHERE name LIKE :search OR description LIKE :search";
  $stmt_categories = $pdo->prepare($sql_categories);
  $stmt_categories->bindParam(':search', $search_query, PDO::PARAM_STR);
  $stmt_categories->execute();
  $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

  $sql_admins .= " WHERE email LIKE :search";
  $stmt_admins = $pdo->prepare($sql_admins);
  $stmt_admins->bindParam(':search', $search_query, PDO::PARAM_STR);
  $stmt_admins->execute();
  $admins = $stmt_admins->fetchAll(PDO::FETCH_ASSOC);
}

// Pagination
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

function paginate($items, $current_page, $items_per_page)
{
  $total_items = count($items);
  $total_pages = ceil($total_items / $items_per_page);
  $start = ($current_page - 1) * $items_per_page;
  $end = $start + $items_per_page;
  $paginated_items = array_slice($items, $start, $items_per_page);

  return [
    'items' => $paginated_items,
    'total_pages' => $total_pages,
    'current_page' => $current_page
  ];
}

$paginated_products = paginate($products, $current_page, $items_per_page);
$paginated_customers = paginate($customers, $current_page, $items_per_page);
$paginated_categories = paginate($categories, $current_page, $items_per_page);
$paginated_admins = paginate($admins, $current_page, $items_per_page);

?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen Data POSTech</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f0f4f8;
      color: #333;
    }

    .header {
      background-color: #2c3e50;
      color: white;
      padding: 2rem;
      text-align: center;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .tab-link {
      padding: 0.5rem 1rem;
      color: #4a5568;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease-in-out;
      border-radius: 0.5rem;
      border: 1px solid transparent;
    }

    .tab-link:hover {
      background-color: #3b82f6;
      color: #ffffff;
    }

    .tab-link.active {
      background-color: #3b82f6;
      color: #ffffff;
    }

    .tab-content {
      display: none;
      padding: 1rem;
      background-color: #ffffff;
      border-radius: 0.5rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      margin-top: 1rem;
    }

    .tab-content.active {
      display: block;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    table th,
    table td {
      padding: 1rem;
      border: 1px solid #e2e8f0;
      text-align: left;
    }

    table th {
      background-color: #3b82f6;
      color: #ffffff;
    }

    button {
      background-color: #10b981;
      color: #ffffff;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #059669;
    }

    .modal {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 50;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background-color: #ffffff;
      margin: 4rem auto;
      padding: 2rem;
      border: 1px solid #e2e8f0;
      width: 80%;
      border-radius: 0.5rem;
    }

    .close {
      color: #718096;
      float: right;
      font-size: 1.5rem;
      font-weight: 700;
      cursor: pointer;
    }

    .close:hover {
      color: #1a202c;
    }

    .pagination {
      display: flex;
      justify-content: center;
      margin-top: 1rem;
    }

    .pagination a {
      color: #3b82f6;
      padding: 0.5rem 1rem;
      text-decoration: none;
      transition: background-color .3s;
      border: 1px solid #ddd;
      margin: 0 4px;
    }

    .pagination a.active {
      background-color: #3b82f6;
      color: white;
      border: 1px solid #3b82f6;
    }

    .pagination a:hover:not(.active) {
      background-color: #ddd;
    }
  </style>
</head>

<body class="bg-gray-100">
  <header class="bg-gray-800 text-white py-8 px-4 text-center shadow-lg">
    <h1 class="text-3xl font-bold mb-2">Manajemen Data POSTech</h1>
    <p class="text-lg mb-4">Kelola data Anda dengan efisien dan aman.</p>
    <div>
      <p class="mb-2">Selamat datang, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</p>
      <a href="dashboard.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300 mr-2">Dashboard</a>
      <a href="order.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-300 mr-2">Pesanan</a>
      <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition duration-300">Logout</a>
    </div>
  </header>

  <nav class="bg-white shadow-md p-4 mb-8">
    <div class="container mx-auto flex justify-center space-x-4">
      <button class="tab-link active" data-tab="products">Produk</button>
      <button class="tab-link" data-tab="customers">Customer</button>
      <button class="tab-link" data-tab="categories">Kategori</button>
      <button class="tab-link" data-tab="admins">Admin</button>
    </div>
  </nav>

  <main class="container mx-auto px-4">
    <form action="" method="GET" class="mb-4">
      <input type="text" name="search" placeholder="Cari..." value="<?= htmlspecialchars($search_query) ?>" class="p-2 border rounded">
      <button type="submit" class="bg-blue-500 text-white p-2 rounded">Cari</button>
    </form>

    <div id="products" class="tab-content active">
      <h2 class="text-2xl font-bold mb-4">Data Produk</h2>
      <a href="add/product.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded mb-4 inline-block">Tambah Produk Baru</a>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama Produk</th>
              <th>Kategori</th>
              <th>Harga</th>
              <th>Stok</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($paginated_products['items'] as $product): ?>
              <tr>
                <td><?= htmlspecialchars($product['product_id']) ?></td>
                <td><?= htmlspecialchars($product['product_name']) ?></td>
                <td><?= htmlspecialchars($product['category_name']) ?></td>
                <td><?= htmlspecialchars($product['price']) ?></td>
                <td><?= htmlspecialchars($product['stock']) ?></td>
                <td>
                  <button onclick="openEditModal('products', <?= $product['product_id'] ?>)" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-2 rounded mr-2">Edit</button>
                  <form method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                    <input type="hidden" name="table" value="products">
                    <input type="hidden" name="id" value="<?= $product['product_id'] ?>">
                    <button type="submit" name="delete_data" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="pagination">
        <?php for ($i = 1; $i <= $paginated_products['total_pages']; $i++): ?>
          <a href="?page=<?= $i ?>" class="<?= $i == $paginated_products['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>

    <div id="customers" class="tab-content">
      <h2 class="text-2xl font-bold mb-4">Data Customer</h2>
      <a href="add/customer.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded mb-4 inline-block">Tambah Customer Baru</a>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Telepon</th>
              <th>Alamat</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($paginated_customers['items'] as $customer): ?>
              <tr>
                <td><?= htmlspecialchars($customer['customer_id']) ?></td>
                <td><?= htmlspecialchars($customer['name']) ?></td>
                <td><?= htmlspecialchars($customer['email']) ?></td>
                <td><?= htmlspecialchars($customer['phone']) ?></td>
                <td><?= htmlspecialchars($customer['address']) ?></td>
                <td>
                  <button onclick="openEditModal('customers', <?= $customer['customer_id'] ?>)" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-2 rounded mr-2">Edit</button>
                  <form method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                    <input type="hidden" name="table" value="customers">
                    <input type="hidden" name="id" value="<?= $customer['customer_id'] ?>">
                    <button type="submit" name="delete_data" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="pagination">
        <?php for ($i = 1; $i <= $paginated_customers['total_pages']; $i++): ?>
          <a href="?page=<?= $i ?>" class="<?= $i == $paginated_customers['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>

    <div id="categories" class="tab-content">
      <h2 class="text-2xl font-bold mb-4">Data Kategori</h2>
      <a href="add/category.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded mb-4 inline-block">Tambah Kategori Baru</a>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama Kategori</th>
              <th>Deskripsi</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($paginated_categories['items'] as $category): ?>
              <tr>
                <td><?= htmlspecialchars($category['category_id']) ?></td>
                <td><?= htmlspecialchars($category['category_name']) ?></td>
                <td><?= htmlspecialchars($category['description']) ?></td>
                <td>
                  <button onclick="openEditModal('categories', <?= $category['category_id'] ?>)" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-2 rounded mr-2">Edit</button>
                  <form method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                    <input type="hidden" name="table" value="categories">
                    <input type="hidden" name="id" value="<?= $category['category_id'] ?>">
                    <button type="submit" name="delete_data" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="pagination">
        <?php for ($i = 1; $i <= $paginated_categories['total_pages']; $i++): ?>
          <a href="?page=<?= $i ?>" class="<?= $i == $paginated_categories['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>

    <div id="admins" class="tab-content">
      <h2 class="text-2xl font-bold mb-4">Data Admin</h2>
      <a href="add/admin.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded mb-4 inline-block">Tambah Admin Baru</a>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr>
              <th>ID</th>
              <th>Email</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($paginated_admins['items'] as $admin): ?>
              <tr>
                <td><?= htmlspecialchars($admin['admin_id']) ?></td>
                <td><?= htmlspecialchars($admin['email']) ?></td>
                <td>
                  <button onclick="openEditModal('admins', <?= $admin['admin_id'] ?>)" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-2 rounded mr-2">Edit</button>
                  <form method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                    <input type="hidden" name="table" value="admins">
                    <input type="hidden" name="id" value="<?= $admin['admin_id'] ?>">
                    <button type="submit" name="delete_data" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="pagination">
        <?php for ($i = 1; $i <= $paginated_admins['total_pages']; $i++): ?>
          <a href="?page=<?= $i ?>" class="<?= $i == $paginated_admins['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>
  </main>

  <footer class="bg-gray-800 text-white py-4 mt-8 text-center">
    <p>&copy; <?= date("Y") ?> POSTech. All Rights Reserved.</p>
  </footer>

  <!-- Modal -->
  <div id="modal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <form id="modal-form" method="POST" class="space-y-4">
        <input type="hidden" name="table" id="modal-table">
        <input type="hidden" name="id" id="modal-id">
        <div id="modal-fields" class="space-y-4"></div>
        <button type="submit" name="edit_data" id="modal-submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Simpan</button>
      </form>
    </div>
  </div>

  <script>
    // Tab switching functionality
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');

    tabLinks.forEach(link => {
      link.addEventListener('click', () => {
        tabLinks.forEach(link => link.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));

        link.classList.add('active');
        const activeTab = link.getAttribute('data-tab');
        document.getElementById(activeTab).classList.add('active');
      });
    });

    function openAddModal(table) {
      document.getElementById('modal').style.display = 'block';
      document.getElementById('modal-table').value = table;
      document.getElementById('modal-id').value = '';
      document.getElementById('modal-submit').name = 'add_data';

      let modalFields = '';

      if (table === 'products') {
        modalFields = `
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Produk:</label>
            <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Kategori:</label>
            <select name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              <?php foreach ($categories as $category): ?>
                <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Harga:</label>
            <input type="number" name="price" step="0.01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Stok:</label>
            <input type="number" name="stock" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
        `;
      } else if (table === 'customers') {
        modalFields = `
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Pelanggan:</label>
            <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Email:</label>
            <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Telepon:</label>
            <input type="tel" name="phone" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Alamat:</label>
            <textarea name="address" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
          </div>
        `;
      } else if (table === 'categories') {
        modalFields = `
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Kategori:</label>
            <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Deskripsi:</label>
            <textarea name="description" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
          </div>
        `;
      } else if (table === 'admins') {
        modalFields = `
          <div>
            <label class="block text-sm font-medium text-gray-700">Email:</label>
            <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Password:</label>
            <input type="password" name="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
        `;
      }

      document.getElementById('modal-fields').innerHTML = modalFields;
    }

    function openEditModal(table, id) {
      document.getElementById('modal').style.display = 'block';
      document.getElementById('modal-table').value = table;
      document.getElementById('modal-id').value = id;
      document.getElementById('modal-submit').name = 'edit_data';

      let modalFields = '';

      if (table === 'products') {
        const product = <?= json_encode($products) ?>.find(p => p.product_id === id);
        modalFields = `
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Produk:</label>
            <input type="text" name="name" value="${product.product_name}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Kategori:</label>
            <select name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              <?php foreach ($categories as $category): ?>
                <option value="<?= $category['category_id'] ?>" ${product.category_id === <?= $category['category_id'] ?> ? 'selected' : ''}><?= $category['category_name'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Harga:</label>
            <input type="number" name="price" value="${product.price}" step="0.01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Stok:</label>
            <input type="number" name="stock" value="${product.stock}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
        `;
      } else if (table === 'customers') {
        const customer = <?= json_encode($customers) ?>.find(c => c.customer_id === id);
        modalFields = `
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Pelanggan:</label>
            <input type="text" name="name" value="${customer.name}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Email:</label>
            <input type="email" name="email" value="${customer.email}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Telepon:</label>
            <input type="tel" name="phone" value="${customer.phone}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Alamat:</label>
            <textarea name="address" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">${customer.address}</textarea>
          </div>
        `;
      } else if (table === 'categories') {
        const category = <?= json_encode($categories) ?>.find(c => c.category_id === id);
        modalFields = `
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Kategori:</label>
            <input type="text" name="name" value="${category.category_name}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Deskripsi:</label>
            <textarea name="description" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">${category.description}</textarea>
          </div>
        `;
      } else if (table === 'admins') {
        const admin = <?= json_encode($admins) ?>.find(a => a.admin_id === id);
        modalFields = `
          <div>
            <label class="block text-sm font-medium text-gray-700">Email:</label>
            <input type="email" name="email" value="${admin.email}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Password Baru (kosongkan jika tidak ingin mengubah):</label>
            <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
        `;
      }

      document.getElementById('modal-fields').innerHTML = modalFields;
    }

    function closeModal() {
      document.getElementById('modal').style.display = 'none';
    }
  </script>
</body>

</html>