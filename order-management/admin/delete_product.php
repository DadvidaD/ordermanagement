<?php
// File: admin/delete_product.php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $product_id = $_POST['id'];

    // Get the image filename to delete it from uploads folder
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product) {
        // Delete product from DB
        $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $deleteStmt->execute([$product_id]);

        // Delete image file
        $imagePath = "../uploads/" . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Redirect back to products page
    header("Location: products.php");
    exit();
} else {
    header("Location: products.php");
    exit();
}
