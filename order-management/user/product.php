<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$product_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container mt-5 alert alert-danger'>Product not found.</div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow-sm">
        <div class="row">
            <div class="col-md-5">
                <img src="../uploads/<?php echo $product['image']; ?>" class="img-fluid rounded" alt="Product Image">
            </div>
            <div class="col-md-7">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="text-warning fw-bold fs-4">₱<?php echo number_format($product['price'], 2); ?></p>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                
                <form method="POST" action="add_to_cart.php" class="mb-2">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="input-group mb-3">
                        <label class="input-group-text">Quantity</label>
                        <input type="number" name="quantity" value="1" min="1" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">Add to Cart</button>
                </form>

                <form method="POST" action="checkout.php">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="quantity" value="1" id="orderNowQty">
                    <input type="hidden" name="payment_method" value="cod">
                    <button type="submit" class="btn btn-success w-100">Order Now</button>
                </form>

                <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">Back to Products</a>
            </div>
        </div>
    </div>
</div>

<script>
// Sync quantity between Add to Cart and Order Now
document.querySelector('input[name="quantity"]').addEventListener('input', function() {
    document.getElementById('orderNowQty').value = this.value;
});
</script>
</body>
</html>
