<?php
session_start();
require "db.php";

$error = "";
$success = "";
$formUsername = "";
$formEmail = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $password = (string)($_POST["password"] ?? "");
    $confirmPassword = (string)($_POST["confirm_password"] ?? "");

    $formUsername = $username;
    $formEmail = $email;

    if ($username === "" || $email === "" || $password === "") {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (!hash_equals($password, $confirmPassword)) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if email is already registered (email is UNIQUE in DB)
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "This email is already registered. Please log in instead.";
            } else {
                // Optionally also check username uniqueness
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = "Username already taken.";
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    // Insert including email (and default role 'user' if the column exists)
                    $stmt = $conn->prepare("
                        INSERT INTO users (username, email, password, role)
                        VALUES (?, ?, ?, 'user')
                    ");
                    $stmt->execute([$username, $email, $hash]);
                    header("Location: login.php?registered=1");
                    exit;
                }
            }
        } catch (PDOException $e) {
            $error = "Registration error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Booking System</title>
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
        /* Hard override for readable register form text */
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
    <h3 class="text-center mb-3">Create Account</h3>
    <p class="text-center text-muted mb-4">Sign up to start booking and rating.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($formUsername); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($formEmail); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Register</button>
        <p class="text-center mt-3 mb-0">
            Already have an account?
            <a href="login.php" class="text-decoration-none">Login</a>
        </p>
    </form>
</div>
</body>
</html>