<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all orders with user info
$stmt = $pdo->query("SELECT o.id AS order_id, u.username, o.total_amount, o.payment_method, o.status, o.created_at
                     FROM orders o
                     JOIN users u ON o.user_id = u.id
                     ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Orders - Ordique_OMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fefbf3;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            padding: 80px 20px 30px 20px;
        }

        .table th {
            background-color: #fff3cd;
        }

        .view-btn {
            background-color: #28a745;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }

        .view-btn:hover {
            background-color: #218838;
        }

        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h3 {
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<!-- Logo -->
<div class="logo-header">
    <img src="../assets/logo.png" alt="Logo">
    <span>ORDIQUE OMS</span>
</div>

<!-- Sidebar -->
<div class="sidebar">
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="orders.php" class="active">📦 Orders</a>
    <a href="products.php">🛒 Products</a>
    <a href="profile.php">👤 Profile</a>
    <a href="../auth/logout.php">🚪 Logout</a>
</div>

<!-- Topbar -->
<div class="topbar">
    <span class="dashboard-title">Order Management</span>
</div>

<!-- Main Content -->
<div class="content container">
    <div class="table-container">
        <h3>All Orders</h3>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Ordered At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($order['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($order['payment_method'])); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($order['status'])); ?></td>
                                <td><?php echo date("F j, Y, g:i a", strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a class="view-btn" href="view_order.php?order_id=<?php echo urlencode($order['order_id']); ?>">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
