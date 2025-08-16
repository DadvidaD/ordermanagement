<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$payment_method = $_POST['payment_method'] ?? 'cod';

try {
    $pdo->beginTransaction();

    // Check if this is a direct "Order Now" purchase
    if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
        $product_id = $_POST['product_id'];
        $quantity = (int) $_POST['quantity'];

        // Get product details
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new Exception("Product not found.");
        }

        $total_amount = $product['price'] * $quantity;

        // Insert into orders table
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, payment_method, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $total_amount, $payment_method]);
        $order_id = $pdo->lastInsertId();

        // Insert single item into order_items
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $product_id, $quantity, $product['price']]);
    } else {
        // Process cart checkout
        $stmt = $pdo->prepare("SELECT c.product_id, p.name, p.price, c.quantity
                               FROM cart c
                               JOIN products p ON c.product_id = p.id
                               WHERE c.user_id = ?");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll();

        if (empty($cart_items)) {
            header("Location: cart.php");
            exit();
        }

        $total_amount = 0;
        foreach ($cart_items as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, payment_method, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $total_amount, $payment_method]);
        $order_id = $pdo->lastInsertId();

        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }

        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }

    $pdo->commit();
    header("Location: order_success.php");
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Order Failed: " . $e->getMessage();
}
?>
