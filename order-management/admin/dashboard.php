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

// Count pending and completed orders and calculate total sales
$pendingOrders = 0;
$completedOrders = 0;
$totalSales = 0;
$dailySales = [];

foreach ($orders as $order) {
    if (strtolower($order['status']) === 'pending') {
        $pendingOrders++;
    } elseif (strtolower($order['status']) === 'completed') {
        $completedOrders++;
        $totalSales += $order['total_amount']; // Only completed count towards sales
    }

    $date = date("Y-m-d", strtotime($order['created_at']));
    if (!isset($dailySales[$date])) {
        $dailySales[$date] = 0;
    }
    $dailySales[$date] += $order['total_amount'];
}
ksort($dailySales);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Ordique_OMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .summary-boxes {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-box {
            flex: 1;
            background: #fff3cd;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .summary-box h4 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .summary-box p {
            margin: 5px 0 0;
            color: #666;
        }

        .chart-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        canvas {
            width: 100% !important;
            height: auto !important;
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
        <a href="dashboard.php" class="active">📊 Dashboard</a>
        <a href="orders.php">📦 Orders</a>
        <a href="products.php">🛒 Products</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../auth/logout.php">🚪 Logout</a>
    </div>

    <!-- Topbar -->
    <div class="topbar">
        <span class="dashboard-title">Dashboard</span>
    </div>

    <!-- Content -->
    <div class="content container">
        <!-- First row: Pending & Completed -->
        <div class="summary-boxes">
            <div class="summary-box">
                <h4><?php echo $pendingOrders; ?></h4>
                <p>Pending Orders</p>
            </div>
            <div class="summary-box">
                <h4><?php echo $completedOrders; ?></h4>
                <p>Completed Orders</p>
            </div>
        </div>

        <!-- Second row: Total Sales -->
        <div class="summary-boxes">
            <div class="summary-box">
                <h4>₱<?php echo number_format($totalSales, 2); ?></h4>
                <p>Total Sales</p>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="chart-container">
            <h5>Sales Overview (Daily)</h5>
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Chart Script -->
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($dailySales)); ?>,
                datasets: [{
                    label: 'Daily Sales (₱)',
                    data: <?php echo json_encode(array_values($dailySales)); ?>,
                    backgroundColor: '#ffc107',
                    borderRadius: 5,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
