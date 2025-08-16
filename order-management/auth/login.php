<?php
// File: auth/login.php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../user/dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .auth-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-form {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-logo {
            display: block;
            margin: 0 auto 20px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ffc107;
        }
        .error {
            color: red;
            text-align: center;
        }
        .success {
            color: green;
            text-align: center;
        }
        .small-text {
            font-size: 0.9rem;
            text-align: center;
            margin-top: 15px;
        }
        .input-group-text {
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <form class="login-form" method="POST" action="">
        <img src="../assets/logo.png" alt="Logo" class="login-logo">
        <h2 class="text-center mb-4">Login</h2>

        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (!empty($_SESSION['message'])): ?>
            <p class="success"><?= $_SESSION['message']; ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>

        <div class="mb-3">
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                <span class="input-group-text" id="togglePassword">🔒</span>
            </div>
        </div>

        <button type="submit" class="btn btn-warning w-100">Login</button>

        <p class="small-text mt-3">
            Don't have an account? <a href="register.php">Register</a>
        </p>
    </form>
</div>

<script>
    const toggle = document.getElementById("togglePassword");
    const password = document.getElementById("password");

    toggle.addEventListener("click", function () {
        const type = password.getAttribute("type") === "password" ? "text" : "password";
        password.setAttribute("type", type);
        this.textContent = type === "password" ? "🔒" : "🔓";
    });
</script>

</body>
</html>
