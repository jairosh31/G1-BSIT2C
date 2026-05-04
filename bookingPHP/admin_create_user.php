<?php
// Admin-only: create user accounts
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = "";
$formUsername = "";
$formEmail = "";
$formRole = "user";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = (string)($_POST['password'] ?? "");
    $confirmPassword = (string)($_POST['confirm_password'] ?? "");
    $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

    $formUsername = $username;
    $formEmail = $email;
    $formRole = $role;

    if ($username === "" || $email === "" || $password === "" || $confirmPassword === "") {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (!hash_equals($password, $confirmPassword)) {
        $error = "Passwords do not match.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "This email is already registered.";
            } else {
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = "Username already taken.";
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $hash, $role]);
                    header("Location: admin_users.php?created=1");
                    exit;
                }
            }
        } catch (PDOException $e) {
            $error = "Error creating user: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Create User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="theme.css">
    <style>
        body { min-height: 100vh; }
        body .container { position: relative; z-index: 1; }
        body .text-muted { color: rgba(255,255,255,0.9) !important; }
        body h2 { color: #fff; }
        .dashboard-card {
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            border: none;
        }
        .booking-layout {
            min-height: 100vh;
            display: flex;
        }
        .booking-main {
            flex: 1;
            min-width: 0;
        }
        .booking-topbar {
            background: rgba(13, 24, 43, 0.55);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        .booking-topbar__brand {
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .booking-topbar__toggler {
            border-color: rgba(255, 255, 255, 0.22);
        }
        .booking-sidebar {
            width: 280px;
            flex: 0 0 280px;
            min-height: 100vh;
            background: rgba(13, 24, 43, 0.55);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #fff;
        }
        .booking-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.88);
            border-radius: 12px;
            padding: 0.55rem 0.75rem;
        }
        .booking-sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.12);
        }
        .booking-sidebar .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.18);
            font-weight: 600;
        }
        .booking-sidebar__brand {
            color: #fff;
            font-weight: 800;
            letter-spacing: 0.2px;
            font-size: 1.1rem;
        }
        .booking-sidebar__subtitle {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.9rem;
        }
        .booking-sidebar__footer {
            border-top: 1px solid rgba(255, 255, 255, 0.10);
        }
        .booking-sidebar__username {
            font-weight: 700;
            line-height: 1.1;
        }
        .booking-sidebar__role {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.85rem;
            text-transform: lowercase;
        }
    </style>
</head>
<body class="booking-bg">
<div class="booking-layout">
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <div class="booking-main">
        <div class="container py-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-0">Create account</h4>
                            <small class="text-muted">Admin only</small>
                        </div>
                        <a href="admin_users.php" class="btn btn-outline-secondary btn-sm">Back</a>
                    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($formUsername); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="user" <?php echo $formRole === 'user' ? 'selected' : ''; ?>>user</option>
                    <option value="admin" <?php echo $formRole === 'admin' ? 'selected' : ''; ?>>admin</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($formEmail); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
        </div>

       <button type="submit" class="btn btn-primary w-100 mt-3">Create account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

