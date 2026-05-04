<?php
// Simple admin dashboard – only for users with role 'admin'
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Get statistics
$stats = [
    'total_users' => 0,
    'total_events' => 0,
    'total_tickets' => 0,
    'active_tickets' => 0,
    'cancelled_tickets' => 0,
    'total_ratings' => 0,
];

try {
    // Total users
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = (int)$stmt->fetchColumn();

    // Total events
    $stmt = $conn->query("SELECT COUNT(*) FROM events");
    $stats['total_events'] = (int)$stmt->fetchColumn();

    // Total tickets
    $stmt = $conn->query("SELECT COUNT(*) FROM tickets");
    $stats['total_tickets'] = (int)$stmt->fetchColumn();

    // Active tickets
    $stmt = $conn->query("SELECT COUNT(*) FROM tickets WHERE status = 'active'");
    $stats['active_tickets'] = (int)$stmt->fetchColumn();

    // Cancelled tickets
    $stmt = $conn->query("SELECT COUNT(*) FROM tickets WHERE status = 'cancelled'");
    $stats['cancelled_tickets'] = (int)$stmt->fetchColumn();

    // Total ratings
    $stmt = $conn->query("SELECT COUNT(*) FROM ratings");
    $stats['total_ratings'] = (int)$stmt->fetchColumn();
} catch (PDOException $e) {
    // Ignore errors for stats
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Booking System</title>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0" style="color: #ffffff !important;">Admin dashboard</h2>
                    <small class="text-muted" style="color: #ffffff !important;">Manage users, tickets and ratings</small>
                </div>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Back to user view</a>
            </div>

            <div class="alert alert-info mb-4">
                Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                (role: <code>admin</code>)
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card h-100 dashboard-card bg-primary text-white">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Total Users</h5>
                            <p class="card-text fs-1 fw-bold flex-grow-1 d-flex align-items-center justify-content-center mb-0">
                                <?php echo number_format($stats['total_users']); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 dashboard-card bg-success text-white">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Total Events</h5>
                            <p class="card-text fs-1 fw-bold flex-grow-1 d-flex align-items-center justify-content-center mb-0">
                                <?php echo number_format($stats['total_events']); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 dashboard-card bg-info text-white">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Active Tickets</h5>
                            <p class="card-text fs-1 fw-bold flex-grow-1 d-flex align-items-center justify-content-center mb-0">
                                <?php echo number_format($stats['active_tickets']); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 dashboard-card bg-warning text-dark">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Total Ratings</h5>
                            <p class="card-text fs-1 fw-bold flex-grow-1 d-flex align-items-center justify-content-center mb-0">
                                <?php echo number_format($stats['total_ratings']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100 dashboard-card">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Total Tickets</h5>
                            <p class="card-text fs-1 fw-bold flex-grow-1 d-flex align-items-center justify-content-center mb-0">
                                <?php echo number_format($stats['total_tickets']); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 dashboard-card">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Cancelled Tickets</h5>
                            <p class="card-text fs-1 fw-bold flex-grow-1 d-flex align-items-center justify-content-center mb-0">
                                <?php echo number_format($stats['cancelled_tickets']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

