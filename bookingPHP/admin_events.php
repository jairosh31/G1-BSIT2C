<?php
// Admin view + creation of concert events
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? "");
    $eventDate = trim($_POST['event_date'] ?? "");
    $location = trim($_POST['location'] ?? "");
    $regularPrice = (float)($_POST['regular_price'] ?? 0);
    $vipPrice = (float)($_POST['vip_price'] ?? 0);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    // Fallback price set to regular price
    $price = $regularPrice;

    if ($name === "" || $eventDate === "" || $location === "" || $regularPrice <= 0 || $vipPrice <= 0) {
        $error = "Please fill in all fields and set both prices greater than 0.";
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO events (name, event_date, location, price, regular_price, vip_price, is_featured)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $eventDate, $location, $price, $regularPrice, $vipPrice, $isFeatured]);
            $success = "Event added successfully.";
        } catch (PDOException $e) {
            $error = "Error adding event: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Load existing events
try {
    $stmt = $conn->query("SELECT id, name, event_date, location, price, regular_price, vip_price, is_featured, created_at FROM events ORDER BY event_date ASC, id ASC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $events = [];
    $error = $error ?: ("Error loading events: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Concert events</title>
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
        /* Hard override for readable events table text */
        .table-responsive .table {
            --bs-table-color: #000 !important;
            --bs-table-bg: #ffffff !important;
            --bs-table-striped-color: #000 !important;
            --bs-table-striped-bg: #f3f4f6 !important;
            --bs-table-hover-color: #000 !important;
            --bs-table-hover-bg: #e5e7eb !important;
        }
        .table-responsive .table tbody td,
        .table-responsive .table tbody th,
        .table-responsive .table tbody td *,
        .table-responsive .table tbody th * {
            color: #000 !important;
        }
        .table-responsive .table thead th {
            color: #fff !important;
        }
        /* Hard override for readable event form text */
        .card .card-body,
        .card .card-body h1,
        .card .card-body h2,
        .card .card-body h3,
        .card .card-body h4,
        .card .card-body h5,
        .card .card-body p,
        .card .card-body small,
        .card .card-body label,
        .card .card-body span,
        .card .card-body div {
            color: #000 !important;
        }
        .card .card-body .form-control,
        .card .card-body .form-select,
        .card .card-body input,
        .card .card-body select,
        .card .card-body textarea {
            background: #fff !important;
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
        }
        .card .card-body .form-control::placeholder {
            color: #6b7280 !important;
        }
    </style>
</head>
<body class="booking-bg">
<div class="booking-layout">
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <div class="booking-main">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0 text-white" style="color: #ffffff !important;">Concert events</h3>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">User view</a>
            </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Add new concert event</h5>
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Concert name" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="event_date" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Regular (₱)</label>
                    <input type="number" name="regular_price" min="0" step="0.01" class="form-control" placeholder="0.00" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">VIP (₱)</label>
                    <input type="number" name="vip_price" min="0" step="0.01" class="form-control" placeholder="0.00" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" placeholder="Venue / city" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                        <label class="form-check-label" for="is_featured">
                            Mark as featured (recommended)
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Add event</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Existing events</h5>
            <?php if (empty($events)): ?>
                <p class="mb-0">No events have been added yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0 bg-white">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Regular</th>
                            <th>VIP</th>
                            <th>Featured</th>
                            <th>Created at</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($events as $ev): ?>
                            <tr>
                                <td><?php echo (int)$ev['id']; ?></td>
                                <td><?php echo htmlspecialchars($ev['name']); ?></td>
                                <td><?php echo htmlspecialchars($ev['event_date']); ?></td>
                                <td><?php echo htmlspecialchars($ev['location']); ?></td>
                                <td>₱<?php echo number_format((float)$ev['regular_price'], 2); ?></td>
                                <td>₱<?php echo number_format((float)$ev['vip_price'], 2); ?></td>
                                <td>
                                    <?php if ((int)$ev['is_featured'] === 1): ?>
                                        <span class="badge bg-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($ev['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

