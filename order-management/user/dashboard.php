<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();

// Cart count
$user_id = $_SESSION['user_id'];
$cartStmt = $pdo->prepare("SELECT SUM(quantity) AS item_count FROM cart WHERE user_id = ?");
$cartStmt->execute([$user_id]);
$cartCount = $cartStmt->fetch()['item_count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - Ordique_OMS</title>
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

        .product-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 15px;
            background-color: #fff;
            transition: box-shadow 0.2s;
            height: 100%;
        }

        .product-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .product-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .product-card h5 {
            margin-bottom: 10px;
        }

        .btn-add {
            background-color: #ffc107;
            border: none;
        }

        .btn-add:hover {
            background-color: #e0a800;
        }
    </style>
</head>
<body>
    <!-- Logo at top-left -->
    <div class="logo-header">
        <img src="../assets/logo.png" alt="Logo">
        <span>ORDIQUE OMS</span>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php" class="active">🏠 Dashboard</a>
        <a href="orders.php">📦 My Orders</a>
        <a href="cart.php">🛒 My Cart 
            <?php if ($cartCount > 0): ?>
                <span class="badge"><?php echo $cartCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="profile.php">👤 Profile</a>
        <a href="../auth/logout.php">🚪 Logout</a>
    </div>

    <!-- Topbar -->
    <div class="topbar">
        <span class="dashboard-title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
    </div>

    <!-- Main Content -->
    <div class="content container mt-4">
        <h3 class="mb-4">Available Products</h3>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4">
                    <div class="product-card h-100">
                        <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" class="product-img" alt="Product">
                        <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="text-warning fw-bold">₱<?php echo number_format($product['price'], 2); ?></p>
                        <form method="POST" action="add_to_cart.php" class="d-flex justify-content-between align-items-center">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="number" name="quantity" value="1" min="1" class="form-control w-25 me-2">
                            <button type="submit" class="btn btn-add">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
