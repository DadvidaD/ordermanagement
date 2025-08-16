<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products - Ordique_OMS</title>
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

        .card img {
            height: 150px;
            object-fit: cover;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: bold;
        }

        .price {
            color: #28a745;
            font-weight: bold;
        }

        .btn-sm {
            font-size: 0.8rem;
            padding: 4px 8px;
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
    <a href="orders.php">📦 Orders</a>
    <a href="products.php" class="active">🛒 Products</a>
    <a href="profile.php">👤 Profile</a>
    <a href="../auth/logout.php">🚪 Logout</a>
</div>

<!-- Topbar -->
<div class="topbar">
    <span class="dashboard-title">Manage Products</span>
</div>

<!-- Main Content -->
<div class="content container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Products</h3>
        <a href="add_product.php" class="btn btn-warning">+ Add Product</a>
    </div>

    <?php if (empty($products)): ?>
        <div class="text-center text-muted">No products available.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm">
                        <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="Product Image">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="price mb-3">₱<?php echo number_format($product['price'], 2); ?></p>
                            <div class="mt-auto">
                                <a href="edit_product.php?id=<?php echo urlencode($product['id']); ?>" class="btn btn-sm btn-primary w-100 mb-2">Edit</a>
                                <form method="POST" action="delete_product.php" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger w-100">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
