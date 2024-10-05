<?php
// Memulai sesi
session_start();
include '../private/middleware/app.php';

// Fungsi untuk memeriksa login dan mengarahkan ke halaman login jika belum login
function checkLogin()
{
    if (!isset($_SESSION['admin_name'])) {
        //$_SESSION['login_error'] = "Anda belum login. Silakan login untuk melanjutkan.";
        header("Location: login.php");
        exit;
    }
}

// Fungsi untuk menampilkan pesan error
function displayErrorMessage()
{
    if (isset($_SESSION['login_error'])) {
        echo '<div class="alert alert-danger" role="alert">' . $_SESSION['login_error'] . '</div>';
        unset($_SESSION['login_error']);
    }
}

// Fungsi untuk inisialisasi token CSRF
function initCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Fungsi untuk mengambil data dari database
function fetchData($pdo, $query)
{
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching data: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk memproses pesanan
function processOrder($pdo)
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_id']) && isset($_POST['customer_id'])) {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die("Validasi token CSRF gagal");
        }

        $adminId = htmlspecialchars(trim($_POST['admin_id']));
        $customerId = htmlspecialchars(trim($_POST['customer_id']));
        $orderedProducts = json_decode($_POST['ordered_products'], true);

        $totalPayment = array_reduce($orderedProducts, function ($carry, $product) {
            return $carry + ($product['price'] * $product['quantity']);
        }, 0);

        $totalProduct = array_reduce($orderedProducts, function ($carry, $product) {
            return $carry + $product['quantity'];
        }, 0);

        try {
            $pdo->beginTransaction();

            // Update stok produk
            foreach ($orderedProducts as $product) {
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$product['quantity'], $product['id']]);
            }

            // Masukkan pesanan baru
            $stmt = $pdo->prepare("INSERT INTO orders (admin_id, customer_id, total_payment, total_product) VALUES (?, ?, ?, ?)");
            $stmt->execute([$adminId, $customerId, $totalPayment, $totalProduct]);
            $orderId = $pdo->lastInsertId();

            // Masukkan detail produk pesanan
            $stmt = $pdo->prepare("INSERT INTO order_products (order_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)");
            foreach ($orderedProducts as $product) {
                $itemTotalPrice = $product['price'] * $product['quantity'];
                $stmt->execute([$orderId, $product['id'], $product['quantity'], $itemTotalPrice]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Pesanan berhasil diproses!']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errorMessage = $e->getCode() == '22003' ? 'Total pembayaran melebihi batas maksimum yang diizinkan.' : 'Gagal memproses pesanan: ' . $e->getMessage();
            echo json_encode(['status' => 'error', 'message' => $errorMessage]);
        }
        exit;
    }
}

// Jalankan fungsi-fungsi
checkLogin();
displayErrorMessage();
initCSRFToken();

// Mengambil data
$categories = fetchData($pdo, "SELECT * FROM categories");
$products = fetchData($pdo, "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id");
$admins = fetchData($pdo, "SELECT * FROM admins");
$customers = fetchData($pdo, "SELECT * FROM customers");

// Proses pesanan jika ada
processOrder($pdo);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Arial', sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .product-card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .category-button {
            transition: all 0.3s ease;
        }

        .category-button:hover {
            transform: scale(1.05);
        }

        #orderSummary {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .quantity-button {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .quantity-button:hover {
            transform: scale(1.1);
        }

        .product-quantity {
            width: 40px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container mx-auto p-5">
        <h1 class="text-4xl font-bold text-center mb-6 text-indigo-700">Daftar Produk</h1>
        <div class="mb-4">
            <input type="text" id="productSearch" placeholder="Cari produk..." class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="mb-6 flex flex-wrap gap-2 justify-center">
            <button class="category-button bg-gray-500 text-white py-2 px-4 rounded-full hover:bg-gray-600 transition duration-200" data-category="all">Tampilkan Semua</button>
            <?php foreach ($categories as $category): ?>
                <button class="category-button bg-indigo-500 text-white py-2 px-4 rounded-full hover:bg-indigo-600 transition duration-200" data-category="<?php echo htmlspecialchars($category['name']); ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="mb-4 text-right">
            <a href="dashboard.php" class="bg-blue-500 text-white font-semibold py-2 px-4 rounded hover:bg-blue-600 transition duration-200">Dashboard</a>
            <a href="master_data.php" class="bg-green-500 text-white font-semibold py-2 px-4 rounded hover:bg-green-600 transition duration-200">Master Data</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
                <div class="border rounded-lg p-4 bg-white shadow-lg product-card transition-transform transform hover:scale-105 cursor-pointer"
                    data-category="<?php echo htmlspecialchars($product['category_name']); ?>"
                    data-id="<?php echo $product['id']; ?>"
                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                    data-price="<?php echo $product['price']; ?>"
                    data-stock="<?php echo $product['stock']; ?>">
                    <h4 class="text-lg font-semibold mb-2 text-indigo-700"><?php echo htmlspecialchars($product['name']); ?></h4>
                    <p class="text-lg font-bold text-green-600">Rp<?php echo number_format($product['price'], 2, ',', '.'); ?></p>
                    <p class="text-sm text-gray-600">Stok: <?php echo htmlspecialchars($product['stock']); ?></p>
                    <p class="text-sm text-gray-500 mt-1">Kategori: <?php echo htmlspecialchars($product['category_name']); ?></p>
                    <div class="flex items-center justify-between mt-2">
                        <button class="decrease-quantity quantity-button bg-red-500 text-white hover:bg-red-600 transition duration-200">-</button>
                        <input type="number" min="0" max="<?php echo $product['stock']; ?>" value="0" class="product-quantity border-t border-b border-gray-300 text-center" readonly>
                        <button class="increase-quantity quantity-button bg-green-500 text-white hover:bg-green-600 transition duration-200">+</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-6">
            <h2 class="text-2xl font-semibold mb-4 text-indigo-700">Rincian Pesanan</h2>
            <form id="orderForm" class="bg-white p-4 rounded-lg shadow-md">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Pilih Admin</label>
                    <select id="admin_id" name="admin_id" required class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Admin --</option>
                        <?php foreach ($admins as $admin): ?>
                            <option value="<?php echo $admin['id']; ?>"><?php echo htmlspecialchars($admin['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Pilih Pelanggan</label>
                    <select id="customer_id" name="customer_id" required class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Pelanggan --</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="orderSummary" class="mb-4"></div>
                <button type="submit" class="bg-indigo-500 text-white p-3 rounded-lg hover:bg-indigo-600 transition duration-200 w-full">Kirim Pesanan</button>
            </form>
        </div>
    </div>

    <script>
        let orderItems = [];

        document.addEventListener('DOMContentLoaded', () => {
            const products = document.querySelectorAll('.product-card');

            products.forEach(product => {
                product.addEventListener('click', () => {
                    const quantityInput = product.querySelector('.product-quantity');
                    const currentQuantity = parseInt(quantityInput.value);
                    const maxStock = parseInt(product.getAttribute('data-stock'));
                    if (currentQuantity < maxStock) {
                        quantityInput.value = currentQuantity + 1;
                        updateOrderItems(product);
                    } else {
                        Swal.fire({
                            title: 'Stok Habis!',
                            text: 'Maaf, stok produk ini telah habis.',
                            icon: 'warning',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    }
                });

                const decreaseButton = product.querySelector('.decrease-quantity');
                const increaseButton = product.querySelector('.increase-quantity');
                const quantityInput = product.querySelector('.product-quantity');

                decreaseButton.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (parseInt(quantityInput.value) > 0) {
                        quantityInput.value = parseInt(quantityInput.value) - 1;
                        updateOrderItems(product);
                    }
                });

                increaseButton.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const maxStock = parseInt(product.getAttribute('data-stock'));
                    if (parseInt(quantityInput.value) < maxStock) {
                        quantityInput.value = parseInt(quantityInput.value) + 1;
                        updateOrderItems(product);
                    } else {
                        Swal.fire({
                            title: 'Stok Habis!',
                            text: 'Maaf, stok produk ini telah habis.',
                            icon: 'warning',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            // Tambahkan kode ini di dalam event listener 'DOMContentLoaded'
            document.getElementById('productSearch').addEventListener('input', function() {
                const query = this.value.toLowerCase();
                const products = document.querySelectorAll('.product-card');
                const categories = document.querySelectorAll('.category-button');
                const admins = document.querySelectorAll('#admin_id option');
                const customers = document.querySelectorAll('#customer_id option');

                // Filter produk
                products.forEach(product => {
                    const productName = product.getAttribute('data-name').toLowerCase();
                    const productCategory = product.getAttribute('data-category').toLowerCase();
                    if (productName.includes(query) || productCategory.includes(query)) {
                        product.style.display = 'block';
                    } else {
                        product.style.display = 'none';
                    }
                });

                // Filter kategori
                categories.forEach(category => {
                    const categoryName = category.getAttribute('data-category').toLowerCase();
                    if (categoryName.includes(query)) {
                        category.style.display = 'block';
                    } else {
                        category.style.display = 'none';
                    }
                });

                // Filter admin
                const adminSelect = document.getElementById('admin_id');
                const adminOptions = adminSelect.options;
                const filteredAdmins = Array.from(adminOptions).filter(option => option.textContent.toLowerCase().includes(query));
                adminSelect.innerHTML = '';
                filteredAdmins.forEach(option => adminSelect.appendChild(option));

                // Filter pelanggan
                const customerSelect = document.getElementById('customer_id');
                const customerOptions = customerSelect.options;
                const filteredCustomers = Array.from(customerOptions).filter(option => option.textContent.toLowerCase().includes(query));
                customerSelect.innerHTML = '';
                filteredCustomers.forEach(option => customerSelect.appendChild(option));
            });

            document.getElementById('orderForm').addEventListener('submit', async (e) => {
                e.preventDefault();

                const adminId = document.getElementById('admin_id').value;
                const customerId = document.getElementById('customer_id').value;

                if (orderItems.length === 0) {
                    Swal.fire({
                        title: 'Pesanan kosong!',
                        text: 'Silakan tambahkan produk ke pesanan.',
                        icon: 'warning',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const orderData = {
                    admin_id: adminId,
                    customer_id: customerId,
                    ordered_products: JSON.stringify(orderItems),
                    csrf_token: document.querySelector('input[name="csrf_token"]').value
                };

                try {
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams(orderData)
                    });

                    const result = await response.json();
                    Swal.fire({
                        title: result.status === 'success' ? 'Berhasil!' : 'Gagal!',
                        text: result.message,
                        icon: result.status === 'success' ? 'success' : 'error',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                    if (result.status === 'success') {
                        orderItems = [];
                        updateOrderSummary();
                        resetQuantities();
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Terjadi kesalahan saat memproses pesanan.',
                        icon: 'error',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        function updateOrderItems(product) {
            const productId = product.getAttribute('data-id');
            const productName = product.getAttribute('data-name');
            const productPrice = parseFloat(product.getAttribute('data-price'));
            const quantity = parseInt(product.querySelector('.product-quantity').value);

            const existingItemIndex = orderItems.findIndex(item => item.id === productId);
            if (existingItemIndex !== -1) {
                if (quantity === 0) {
                    orderItems.splice(existingItemIndex, 1);
                } else {
                    orderItems[existingItemIndex].quantity = quantity;
                }
            } else if (quantity > 0) {
                orderItems.push({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: quantity
                });
            }

            updateOrderSummary();
        }

        function updateOrderSummary() {
            const orderSummary = document.getElementById('orderSummary');
            orderSummary.innerHTML = '';
            let total = 0;

            if (orderItems.length === 0) {
                orderSummary.innerHTML = '<p class="text-gray-500">Tidak ada produk yang dipilih.</p>';
            } else {
                orderItems.forEach(item => {
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;
                    orderSummary.innerHTML += `<div class="mb-2">${item.name} - ${item.quantity} pcs: <span class="font-semibold">Rp${itemTotal.toLocaleString('id-ID')}</span></div>`;
                });
                orderSummary.innerHTML += `<div class="mt-4 text-xl font-bold text-indigo-700">Total: Rp${total.toLocaleString('id-ID')}</div>`;
            }
        }

        function resetQuantities() {
            document.querySelectorAll('.product-quantity').forEach(input => {
                input.value = 0;
            });
        }
    </script>
</body>

</html>