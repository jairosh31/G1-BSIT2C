<?php
session_start();
require 'db.php';
$message = "";

if (($_GET['registered'] ?? '') === '1') {
    $message = "<div class='alert alert-success'>Account created successfully. Please sign in.</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Success! Store user info in the session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect admins to admin dashboard, others to normal dashboard
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Invalid email or password.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger text-truncate'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="theme.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            max-width: 420px;
            width: 100%;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.18);
            border: none;
            padding: 28px 30px 26px;
        }
        /* Hard override for readable login form text */
        .auth-card,
        .auth-card h1,
        .auth-card h2,
        .auth-card h3,
        .auth-card h4,
        .auth-card h5,
        .auth-card p,
        .auth-card small,
        .auth-card label,
        .auth-card span,
        .auth-card div {
            color: #000 !important;
        }
        .auth-card .form-control,
        .auth-card input,
        .auth-card textarea,
        .auth-card select {
            background: #fff !important;
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
        }
        .auth-card .form-control::placeholder {
            color: #6b7280 !important;
        }
    </style>
</head>
<body class="booking-bg">
<div class="auth-card">
    <h3 class="text-center mb-3">Login</h3>
    <p class="text-center text-muted mb-4">Welcome back, please sign in.</p>
    <?php echo $message; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Sign In</button>
        <p class="text-center mt-3 mb-0">New here? <a href="register.php" class="text-decoration-none">Create Account</a></p>
    </form>
</div>
</body>
</html>