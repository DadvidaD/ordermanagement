<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit();
}

// Include the database connection
require '../config/db.php';

// Check if the cart session is not empty
if (empty($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header("Location: cart.php"); // Redirect to cart if it's empty
    exit();
}

// Calculate total amount
$total_amount = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

// Payment methods (you can add more if needed)
$payment_methods = ['cod' => 'Cash on Delivery', 'credit' => 'Credit Card'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Review</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="order-review-container">
        <h2>Order Review</h2>

        <h3>Items in Your Cart</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                        <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><strong>Total Amount: </strong>₱<?php echo number_format($total_amount, 2); ?></p>

        <h3>Choose Payment Method</h3>
        <form action="checkout.php" method="POST">
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <?php foreach ($payment_methods as $key => $method): ?>
                    <option value="<?php echo $key; ?>"><?php echo $method; ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Proceed to Checkout</button>
        </form>

        <p><a href="cart.php">Edit Cart</a></p>
    </div>
</body>
</html>
