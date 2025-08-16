<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$order_id = $_GET['order_id'];

// Fetch order details
$stmt = $pdo->prepare("SELECT o.id, o.total_amount, o.payment_method, o.status, o.created_at,
                              oi.product_id, oi.quantity, oi.price, p.name AS product_name
                       FROM orders o
                       JOIN order_items oi ON o.id = oi.order_id
                       JOIN products p ON oi.product_id = p.id
                       WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation - Ordique_OMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff8e1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .confirmation {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            color: #ffc107;
        }

        table {
            margin-top: 25px;
        }

        .table th {
            background-color: #fff3cd;
        }

        .details p {
            margin-bottom: 6px;
        }

        .btn-custom {
            background-color: #ffc107;
            color: #000;
            font-weight: bold;
            border: none;
        }

        .btn-custom:hover {
            background-color: #e0a800;
        }

        @media print {
            .btn-print, .btn-back {
                display: none !important;
            }

            body {
                background-color: #fff;
            }

            .confirmation {
                box-shadow: none;
                border: none;
                margin: 0;
                width: 100%;
                padding: 0;
            }
        }
    </style>
</head>
<body>
<div class="confirmation">
    <h1 class="text-center mb-3">Ordique_OMS Services</h1>
    <p class="text-center">Thank you for your purchase, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
    <p class="text-center mb-4">Your order has been successfully placed.</p>

    <h2>Order Summary</h2>
    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_amount = 0;
                foreach ($order as $item):
                    $total = $item['quantity'] * $item['price'];
                    $total_amount += $total;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                        <td>₱<?php echo number_format($total, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="details mt-4">
        <h5>Total: ₱<?php echo number_format($total_amount, 2); ?></h5>
        <p><strong>Payment:</strong> <?php echo ucfirst($order[0]['payment_method']); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($order[0]['status']); ?></p>
        <p><strong>Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($order[0]['created_at'])); ?></p>
    </div>

    <div class="text-center mt-4">
        <button onclick="window.print()" class="btn btn-custom btn-print me-2">🖨️ Print</button>
        <a href="dashboard.php" class="btn btn-custom btn-back">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>
