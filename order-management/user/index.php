<?php
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>This is your user dashboard.</p>

        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard Overview</a></li>
                <li><a href="cart.php">View Cart</a></li>
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</body>
</html>
