<?php
session_start();
include '../private/middleware/app.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Validate CSRF token
  if (!hash_equals($_SESSION['csrf_token'], $_SERVER['HTTP_X_CSRF_TOKEN'])) {
    die(json_encode(['status' => 'error', 'message' => 'CSRF token validation failed']));
  }

  $data = json_decode(file_get_contents('php://input'), true);

  // Validate input data
  $admin_email = filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL);
  $customer_phone = htmlspecialchars(trim($data['customer_phone']));
  $total_payment = floatval($data['total_payment']);
  $total_product = intval($data['total_product']);
  $ordered_products = $data['ordered_products'];

  if (!$admin_email || empty($customer_phone) || $total_payment <= 0 || $total_product <= 0 || !is_array($ordered_products)) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid input data']));
  }

  try {
    // Start transaction
    $pdo->beginTransaction();

    // Insert into orders table
    $stmt = $pdo->prepare("INSERT INTO orders (admin_id, customer_id, total_payment, total_product) VALUES (:admin_id, :customer_id, :total_payment, :total_product)");
    $stmt->execute([
      ':admin_id' => 1, // Adjust this according to your logic
      ':customer_id' => null, // You'll need to get the customer ID from your database if needed
      ':total_payment' => $total_payment,
      ':total_product' => $total_product,
    ]);

    $orderId = $pdo->lastInsertId();

    // Insert ordered products
    foreach ($ordered_products as $ordered) {
      if (!isset($ordered['product_id'], $ordered['quantity'], $ordered['price']) || $ordered['quantity'] <= 0) {
        throw new Exception("Invalid ordered product data");
      }

      $stmt = $pdo->prepare("INSERT INTO order_product (order_id, product_id, quantity, total_price) VALUES (:order_id, :product_id, :quantity, :total_price)");
      $stmt->execute([
        ':order_id' => $orderId,
        ':product_id' => $ordered['product_id'],
        ':quantity' => $ordered['quantity'],
        ':total_price' => $ordered['quantity'] * $ordered['price'], // Assuming you need the price here
      ]);

      // Update product stock
      $stmt = $pdo->prepare("UPDATE products SET stock = stock - :quantity WHERE id = :product_id");
      $stmt->execute([
        ':quantity' => $ordered['quantity'],
        ':product_id' => $ordered['product_id'],
      ]);
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Order placed successfully!', 'updatedStock' => []]); // Add updated stock data if needed
  } catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
  }
} else {
  die(json_encode(['status' => 'error', 'message' => 'Invalid request method']));
}
