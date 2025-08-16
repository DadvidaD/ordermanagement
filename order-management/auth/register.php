<?php
// File: auth/register.php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password !== $password_confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $error = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $email, $hashed_password]);

            $_SESSION['message'] = "Registration successful! You can now log in.";
            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

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
        .toggle-icon {
            cursor: pointer;
            padding-left: 10px;
            font-size: 1.2rem;
            user-select: none;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <form class="login-form" method="POST" action="">
        <h2 class="text-center mb-4">Create an Account</h2>

        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (!empty($_SESSION['message'])): ?>
            <p class="success"><?= $_SESSION['message']; ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>

        <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>

        <div class="mb-3 position-relative">
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
            <span class="toggle-icon position-absolute top-50 end-0 translate-middle-y me-3" onclick="togglePassword('password', this)">🔒</span>
        </div>

        <div class="mb-3 position-relative">
            <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="Confirm Password" required>
            <span class="toggle-icon position-absolute top-50 end-0 translate-middle-y me-3" onclick="togglePassword('password_confirm', this)">🔒</span>
        </div>

        <button type="submit" class="btn btn-warning w-100">Register</button>

        <p class="small-text mt-3">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </form>
</div>

<script>
    function togglePassword(id, icon) {
        const input = document.getElementById(id);
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;
        icon.textContent = type === 'password' ? '🔒' : '🔓';
    }
</script>

</body>
</html>
