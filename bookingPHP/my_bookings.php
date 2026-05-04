<?php
// Shows ratings submitted by the logged-in user
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("
        SELECT rating, comment, created_at
        FROM ratings
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId]);
    $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ratings = [];
    $error = "Error loading your ratings: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Ratings</title>
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
        /* Hard override for readable ratings list text */
        .list-group .list-group-item,
        .list-group .list-group-item *,
        .list-group .list-group-item p,
        .list-group .list-group-item small,
        .list-group .list-group-item .text-muted {
            color: #000 !important;
        }
        .list-group .list-group-item {
            background-color: #fff !important;
        }
    </style>
</head>
<body class="booking-bg">
<div class="booking-layout">
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <div class="booking-main">
        <div class="container py-4">
            <h3 class="mb-3 text-white" style="color: #ffffff !important;">My Ratings</h3>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (empty($ratings)): ?>
        <p>You haven't submitted any ratings yet. <a href="booking.php">Rate your experience</a>.</p>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($ratings as $row): ?>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            <?php
                            $stars = str_repeat("★", (int)$row["rating"]) . str_repeat("☆", 5 - (int)$row["rating"]);
                            ?>
                            <strong><?php echo $stars; ?></strong>
                        </div>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($row["created_at"]); ?>
                        </small>
                    </div>
                    <?php if ($row["comment"]): ?>
                        <p class="mb-0 mt-1"><?php echo nl2br(htmlspecialchars($row["comment"])); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


