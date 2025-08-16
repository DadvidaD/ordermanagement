<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle "Order Received"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['received_order_id'])) {
    $orderId = intval($_POST['received_order_id']);
    $checkStmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$orderId, $user_id]);

    if ($checkStmt->fetch()) {
        $updateStmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        $updateStmt->execute([$orderId]);
        header("Location: orders.php");
        exit();
    }
}

// Fetch orders
$stmt = $pdo->prepare("SELECT o.id, o.total_amount, o.payment_method, o.created_at, o.status 
                       FROM orders o 
                       WHERE o.user_id = ? 
                       ORDER BY o.created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Fetch cart count
$cartStmt = $pdo->prepare("SELECT SUM(quantity) AS item_count FROM cart WHERE user_id = ?");
$cartStmt->execute([$user_id]);
$cartCount = $cartStmt->fetch()['item_count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Orders - Ordique_OMS</title>
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
        .table-responsive {
            margin-top: 20px;
            overflow-x: auto;
        }
        .order-table {
            width: 100%;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .order-table th, .order-table td {
            vertical-align: middle;
            text-align: center;
            padding: 12px 10px;
            word-wrap: break-word;
            white-space: normal;
            max-width: 150px;
        }
        .badge {
            font-size: 0.9rem;
        }
        .details-btn {
            font-size: 0.85rem;
            padding: 4px 12px;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                box-shadow: none;
                padding-top: 50px;
            }
            .topbar {
                margin-left: 0;
            }
            .content {
                margin-left: 0;
                padding-top: 30px;
            }
        }
    </style>
</head>
<body>

<div class="logo-header">
    <img src="../assets/logo.png" alt="Logo" />
    <span>ORDIQUE OMS</span>
</div>

<div class="sidebar">
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="orders.php" class="active">📦 My Orders</a>
    <a href="cart.php">🛒 My Cart 
        <?php if ($cartCount > 0): ?>
            <span class="badge"><?= $cartCount; ?></span>
        <?php endif; ?>
    </a>
    <a href="profile.php">👤 Profile</a>
    <a href="../auth/logout.php">🚪 Logout</a>
</div>

<div class="topbar">
    <span class="dashboard-title">My Orders</span>
</div>

<div class="content container">
    <?php if (empty($orders)): ?>
        <div class="alert alert-info text-center">You haven't placed any orders yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered order-table">
                <thead class="table-warning">
                    <tr>
                        <th>Order ID</th>
                        <th>Total Amount</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($order['id']); ?></td>
                        <td>₱<?= number_format($order['total_amount'], 2); ?></td>
                        <td><?= ucfirst(htmlspecialchars($order['payment_method'])); ?></td>
                        <td><?= date('F j, Y', strtotime($order['created_at'])); ?></td>
                        <td>
                            <?php if ($order['status'] === 'pending'): ?>
                                <form method="post" action="orders.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to mark this order as received?');">
                                    <input type="hidden" name="received_order_id" value="<?= $order['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-warning">Mark as Received</button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-success">Received</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="order_confirmation.php?order_id=<?= htmlspecialchars($order['id']); ?>" class="btn btn-sm btn-outline-primary details-btn">View Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
