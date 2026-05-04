<?php
// List of tickets (bookings) for the logged-in user
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$tickets = [];
$error = "";

try {
    $stmt = $conn->prepare("
        SELECT id, event_name, event_date, event_location, quantity, price, ticket_code, status, created_at, chair_number, seat_type, payment_method
        FROM tickets
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading your tickets: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Tickets</title>
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
        .my-tickets-title {
            color: #fff !important;
            background: transparent !important;
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
                <h3 class="mb-0 my-tickets-title" style="color: #ffffff !important;">My Tickets</h3>
            </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (empty($tickets)): ?>
        <p>You don't have any tickets yet. <a href="ticket_booking.php">Book a new ticket.</a></p>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($tickets as $ticket): ?>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1">
                                <?php echo htmlspecialchars($ticket['event_name']); ?>
                            </h5>
                            <small class="text-muted mb-2">
                                Date: <?php echo htmlspecialchars($ticket['event_date']); ?>
                            </small>
                            <?php if (!empty($ticket['event_location'])): ?>
                                <small class="text-muted mb-2">
                                    Location: <?php echo htmlspecialchars($ticket['event_location']); ?>
                                </small>
                            <?php endif; ?>
                            <p class="mb-1">
                                Tickets: <strong><?php echo (int)$ticket['quantity']; ?></strong>
                            </p>
                            <?php if (!empty($ticket['chair_number'])): ?>
                                <p class="mb-1">
                                    Chair: <strong><?php echo htmlspecialchars($ticket['chair_number']); ?></strong>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($ticket['seat_type'])): ?>
                                <p class="mb-1">
                                    Seat Type: <strong><?php echo htmlspecialchars($ticket['seat_type']); ?></strong>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($ticket['payment_method'])): ?>
                                <p class="mb-1">
                                    Payment: <strong><?php echo htmlspecialchars($ticket['payment_method']); ?></strong>
                                </p>
                            <?php endif; ?>
                            <?php if (isset($ticket['price']) && $ticket['price'] !== null): ?>
                                <p class="mb-1">
                                    Price: <strong>₱<?php echo number_format((float)$ticket['price'], 2); ?></strong> / ticket
                                </p>
                            <?php endif; ?>
                            <p class="mb-1">
                                Ticket code:
                                <span class="fw-semibold text-monospace">
                                    <?php echo htmlspecialchars($ticket['ticket_code']); ?>
                                </span>
                            </p>
                            <p class="mb-2">
                                Status:
                                <?php if ($ticket['status'] === 'cancelled'): ?>
                                    <span class="badge bg-secondary">Cancelled</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </p>
                            <small class="text-muted mb-3">
                                Booked at: <?php echo htmlspecialchars($ticket['created_at']); ?>
                            </small>
                        </div>
                    </div>
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

