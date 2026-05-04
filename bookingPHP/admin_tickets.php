<?php
// Admin view of all tickets
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = "";
try {
    $stmt = $conn->query("
        SELECT t.id, t.event_name, t.event_date, t.event_location, t.quantity, t.price, t.ticket_code, t.status, t.created_at,
               u.username, u.email, t.chair_number, t.seat_type, t.payment_method
        FROM tickets t
        LEFT JOIN users u ON t.user_id = u.id
        ORDER BY t.id ASC
    ");
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading tickets: " . htmlspecialchars($e->getMessage());
    $tickets = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Tickets</title>
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
        /* Hard override for readable tickets table text */
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
    </style>
</head>
<body class="booking-bg">
<div class="booking-layout">
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <div class="booking-main">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0 text-white" style="color: #ffffff !important;">All tickets</h3>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">User view</a>
            </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (empty($tickets)): ?>
        <p>No tickets found.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Chair</th>
                        <th>Seat Type</th>
                        <th>Payment</th>
                        <th>Ticket code</th>
                        <th>Status</th>
                        <th>Created at</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($tickets as $t): ?>
                    <tr>
                        <td><?php echo (int)$t['id']; ?></td>
                        <td><?php echo htmlspecialchars($t['username'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($t['email'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($t['event_name']); ?></td>
                        <td><?php echo htmlspecialchars($t['event_date']); ?></td>
                        <td><?php echo htmlspecialchars($t['event_location'] ?? '—'); ?></td>
                        <td><?php echo (int)$t['quantity']; ?></td>
                        <td>
                            <?php echo isset($t['price']) && $t['price'] !== null ? ('₱' . number_format((float)$t['price'], 2)) : '—'; ?>
                        </td>
                        <td><?php echo htmlspecialchars($t['chair_number'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($t['seat_type'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($t['payment_method'] ?? '—'); ?></td>
                        <td><span class="text-monospace"><?php echo htmlspecialchars($t['ticket_code']); ?></span></td>
                        <td>
                            <?php if ($t['status'] === 'cancelled'): ?>
                                <span class="badge bg-secondary">Cancelled</span>
                            <?php else: ?>
                                <span class="badge bg-success">Active</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($t['created_at']); ?></td>
                        <td>
                            <?php if ($t['status'] !== 'cancelled'): ?>
                                <form method="post" action="cancel_booking.php" class="d-inline">
                                    <input type="hidden" name="ticket_id" value="<?php echo (int)$t['id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

