<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_FILES['image']['name'];
    $target = "../uploads/" . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, image) VALUES (?, ?, ?)");
        $stmt->execute([$name, $price, $image]);
        $message = "Product added successfully!";
    } else {
        $error = "Failed to upload image.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Add Product - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <style>
        body {
            background-color: #fefbf3;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 600px;
            margin-top: 80px;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .btn-yellow {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }
        .btn-yellow:hover {
            background-color: #e0a800;
            border-color: #d39e00;
            color: #000;
        }
        .form-label {
            font-weight: 600;
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
        .back-link {
            margin-top: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>

<div class="logo-header">
    <img src="../assets/logo.png" alt="Logo" />
    <span>ORDIQUE OMS</span>
</div>

<div class="container mt-5">
    <h3 class="text-center mb-4">Add New Product</h3>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success text-center"><?= $message; ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Product Name</label>
            <input type="text" class="form-control" name="name" id="name" required/>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Price (₱)</label>
            <input type="number" class="form-control" name="price" id="price" required step="0.01"/>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Product Image</label>
            <input type="file" class="form-control" name="image" id="image" accept="image/*" required/>
        </div>
        <button type="submit" class="btn btn-yellow w-100">Add Product</button>
    </form>

    <a href="dashboard.php" class="back-link text-decoration-none text-secondary">&larr; Back to Dashboard</a>
</div>

</body>
</html>
