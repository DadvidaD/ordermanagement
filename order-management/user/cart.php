<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT c.id AS cart_id, p.name, p.price, p.image, c.quantity 
                       FROM cart c
                       JOIN products p ON c.product_id = p.id
                       WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Your Cart - Ordique_OMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #fefbf3;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }

        .logo-header {
            position: fixed;
            top: 10px;
            left: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1100;
        }

        .logo-header img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffc107;
        }

        .logo-header span {
            font-weight: bold;
            font-size: 18px;
            color: #333;
        }

        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #fff8dc;
            padding-top: 80px;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            z-index: 999;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 15px 20px;
            color: #333;
            font-weight: 600;
            text-decoration: none;
            border-radius: 0 20px 20px 0;
            transition: all 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #ffe082;
            color: #000;
        }

        .sidebar a.active {
            background-color: #ffc107;
            color: #fff;
        }

        .sidebar .badge {
            background: red;
            color: white;
            font-size: 12px;
            padding: 4px 7px;
            border-radius: 12px;
        }

        .topbar {
            height: 60px;
            background-color: #ffc107;
            color: #000;
            display: flex;
            align-items: center;
            padding: 0 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            margin-left: 250px;
        }

        .dashboard-title {
            font-size: 20px;
            font-weight: bold;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }

        table.cart-table th,
        table.cart-table td {
            vertical-align: middle;
        }

        table.cart-table img {
            width: 60px;
            border-radius: 8px;
        }

        .cart-actions form {
            display: inline-block;
            margin: 0;
        }

        .cart-summary {
            margin-top: 30px;
            padding: 20px;
            background: #fff8e1;
            border-radius: 12px;
            text-align: right;
        }

        .btn-checkout {
            background-color: #28a745;
            color: white;
        }

        .btn-checkout:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <!-- Logo at top-left -->
    <div class="logo-header">
        <img src="../assets/logo.png" alt="Logo" />
        <span>ORDIQUE OMS</span>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="orders.php">📦 My Orders</a>
        <a href="cart.php" class="active">🛒 My Cart</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../auth/logout.php">🚪 Logout</a>
    </div>

    <!-- Topbar -->
    <div class="topbar">
        <span class="dashboard-title">My Cart</span>
    </div>

    <!-- Main Content -->
    <div class="content container mt-4">
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-warning">Your cart is empty.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table cart-table table-bordered align-middle">
                    <thead class="table-warning">
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><img src="../uploads/<?= htmlspecialchars($item['image']); ?>" alt="Product Image" /></td>
                            <td><?= htmlspecialchars($item['name']); ?></td>
                            <td>₱<?= number_format($item['price'], 2); ?></td>
                            <td><?= $item['quantity']; ?></td>
                            <td>₱<?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td class="cart-actions">
                                <form method="POST" action="remove_from_cart.php">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id']; ?>" />
                                    <button class="btn btn-danger btn-sm" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-summary">
                <h3>Total: ₱<?= number_format($total, 2); ?></h3>
                <form action="checkout.php" method="POST" class="row g-2 justify-content-end">
                    <div class="col-auto">
                        <select name="payment_method" class="form-select" required>
                            <option value="">Choose Payment</option>
                            <option value="cod">Cash on Delivery</option>
                            <option value="ewallet">eWallet</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-checkout">Checkout</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
