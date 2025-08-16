<?php
// File: admin/edit_product.php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $product['image'];

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target = "../uploads/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }

    $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, image = ? WHERE id = ?");
    $stmt->execute([$name, $price, $image, $id]);

    header("Location: products.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Edit Product</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" value="<?php echo $product['name']; ?>" required><br>
            <input type="number" name="price" value="<?php echo $product['price']; ?>" step="0.01" required><br>
            <input type="file" name="image" accept="image/*"><br>
            <img src="../uploads/<?php echo $product['image']; ?>" width="100"><br>
            <button type="submit">Update Product</button>
        </form>
        <p><a href="products.php">Back to Product List</a></p>
    </div>
</body>
</html>
