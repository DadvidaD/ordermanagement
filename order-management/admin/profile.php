<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$message = "";

// Fetch admin info
$stmt = $pdo->prepare("SELECT username, email, password, profile_picture FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$user = $stmt->fetch();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = $_POST['username'];
    $newEmail = $_POST['email'];

    $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?")->execute([$newUsername, $newEmail, $admin_id]);

    if (!empty($_POST['new_password'])) {
        $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashedPassword, $admin_id]);
    }

    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = "../uploads/";
        $fileName = basename($_FILES['profile_picture']['name']);
        $targetFile = $targetDir . time() . "_" . $fileName;
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile);
        $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?")->execute([basename($targetFile), $admin_id]);
    }

    $_SESSION['username'] = $newUsername;
    header("Location: profile.php?updated=1");
    exit();
}

if (isset($_GET['updated'])) {
    $message = "Profile updated successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile - Ordique_OMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            padding: 80px 20px 30px 20px;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .profile-picture {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-picture img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ffc107;
        }

        .btn-update {
            background-color: #ffc107;
            color: white;
            border: none;
        }

        .btn-update:hover {
            background-color: #e0a800;
        }

        .message {
            text-align: center;
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="logo-header">
    <img src="../assets/logo.png" alt="Logo">
    <span>ORDIQUE OMS</span>
</div>

<div class="sidebar">
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="orders.php">📦 Orders</a>
    <a href="products.php">🛒 Products</a>
    <a href="profile.php" class="active">👤 Profile</a>
    <a href="../auth/logout.php">🚪 Logout</a>
</div>

<div class="topbar">
    <span class="dashboard-title">My Profile</span>
</div>

<div class="content container">
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-picture">
                <img src="../uploads/<?php echo htmlspecialchars($user['profile_picture'] ?? 'default.png'); ?>" alt="Profile Picture">
            </div>

            <div class="mb-3">
                <label class="form-label">Change Profile Picture</label>
                <input type="file" name="profile_picture" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" value="********" class="form-control" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Change Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Enter new password">
            </div>

            <button type="submit" class="btn btn-update w-100">Update Profile</button>
        </form>
    </div>
</div>

</body>
</html>
