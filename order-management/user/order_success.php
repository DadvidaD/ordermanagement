<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Success</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background-color: #fefefe;
            font-family: Arial, sans-serif;
            padding: 40px;
            text-align: center;
        }

        .success-container {
            background-color: #fffbea;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            display: inline-block;
            max-width: 500px;
            width: 100%;
        }

        .success-container h1 {
            color: #ffcc00;
            margin-bottom: 20px;
        }

        .success-container p {
            font-size: 16px;
            color: #333;
            margin: 10px 0;
        }

        .success-container a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 25px;
            background-color: #ffcc00;
            color: #000;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .success-container a:hover {
            background-color: #e6b800;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <h1>🎉 Order Successful!</h1>
        <p>Thank you for your order, <strong><?php echo $_SESSION['username']; ?></strong>.</p>
        <p>Your items are being prepared for shipment.</p>
        <p>You can view your orders in the dashboard.</p>
        <a href="dashboard.php">Go to Dashboard</a>
    </div>
</body>
</html>
