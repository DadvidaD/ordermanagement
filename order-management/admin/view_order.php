<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

require '../config/db.php';

$order_id = $_GET['order_id'] ?? null;

$stmt = $pdo->prepare("SELECT o.id AS order_id, o.user_id, o.total_amount, o.payment_method, o.status, o.created_at, u.username
                       FROM orders o
                       JOIN users u ON o.user_id = u.id
                       WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

$stmt_items = $pdo->prepare("SELECT oi.product_id, oi.quantity, oi.price, p.name AS product_name
                             FROM order_items oi
                             JOIN products p ON oi.product_id = p.id
                             WHERE oi.order_id = ?");
$stmt_items->execute([$order_id]);
$order_items = $stmt_items->fetchAll();

if (!$order) {
    echo "Order not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>View Order - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #fefbf3;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar */
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px;
            background-color: #fff8dc;
            padding-top: 60px;
            transition: left 0.3s ease;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 999;
        }

        .sidebar a {
            display: block;
            padding: 15px 20px;
            color: #333;
            font-weight: 600;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: #ffe082;
        }

        .wrapper.open-sidebar .sidebar {
            left: 0;
        }

        /* Topbar */
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
        }

        .menu-toggle {
            font-size: 24px;
            cursor: pointer;
            margin-right: 15px;
            user-select: none;
        }

        .dashboard-title {
            font-size: 20px;
            font-weight: bold;
        }

        /* Content */
        .content {
            margin-left: 0;
            padding: 20px;
            transition: margin-left 0.3s ease;
            max-width: 900px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .wrapper.open-sidebar .content {
            margin-left: 250px;
        }

        table thead {
            background-color: #fff3cd;
        }

        .back-btn {
            background-color: #ffc107;
            color: #000;
            text-decoration: none;
            padding: 8px 18px;
            border-radius: 6px;
            display: inline-block;
            margin-top: 20px;
            font-weight: 600;
            transition: background-color 0.2s ease;
        }

        .back-btn:hover {
            background-color: #e0a800;
            color: #fff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper" id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="orders.php">📦 Orders</a>
            <a href="../auth/logout.php">🚪 Logout</a>
        </div>

        <!-- Topbar -->
        <div class="topbar">
            <span class="menu-toggle" onclick="toggleSidebar()">☰</span>
            <span class="dashboard-title">View Order</span>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Order #<?php echo htmlspecialchars($order['order_id']); ?> Details</h2>

            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
            <p><strong>Total Amount:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?></p>
            <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
            <p><strong>Order Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($order['created_at'])); ?></p>

            <h4 class="mt-4">Order Items</h4>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mt-2">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price per Item</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_order_items = 0;
                        foreach ($order_items as $item): 
                            $total_price = $item['quantity'] * $item['price'];
                            $total_order_items += $total_price;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                            <td>₱<?php echo number_format($total_price, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <h5 class="text-end">Total Order Price: ₱<?php echo number_format($total_order_items, 2); ?></h5>

            <a href="orders.php" class="back-btn">← Back to Orders</a>
        </div>
    </div>

<script>
    function toggleSidebar() {
        document.getElementById('wrapper').classList.toggle('open-sidebar');
    }
</script>
</body>
</html>
