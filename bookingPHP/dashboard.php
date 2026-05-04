<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION["user_id"])) {
    echo "Not logged in. <a href='login.php'>Go to login</a>";
    exit;
}

// Load concerts from database: featured = Recommended, rest = Upcoming (only future events)
$recommendedConcerts = [];
$upcomingConcerts = [];
$today = date('Y-m-d');

try {
    // Recommended: featured events with date >= today
    $stmt = $conn->prepare("
        SELECT id, name, event_date AS date, location, price, regular_price, vip_price, is_featured
        FROM events
        WHERE event_date >= ? AND is_featured = 1
        ORDER BY event_date ASC
    ");
    $stmt->execute([$today]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recommendedConcerts[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'date' => $row['date'],
            'location' => $row['location'],
            'price' => (float)$row['price'],
            'regular_price' => (float)$row['regular_price'],
            'vip_price' => (float)$row['vip_price'],
            'tag' => 'Featured',
        ];
    }

    // Upcoming: non-featured events with date >= today
    $stmt = $conn->prepare("
        SELECT id, name, event_date AS date, location, price, regular_price, vip_price
        FROM events
        WHERE event_date >= ? AND (is_featured = 0 OR is_featured IS NULL)
        ORDER BY event_date ASC
    ");
    $stmt->execute([$today]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $upcomingConcerts[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'date' => $row['date'],
            'location' => $row['location'],
            'price' => (float)$row['price'],
            'regular_price' => (float)$row['regular_price'],
            'vip_price' => (float)$row['vip_price'],
            'tag' => 'Upcoming',
        ];
    }
} catch (PDOException $e) {
    // Keep empty lists on error; optional: log $e->getMessage()
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="theme.css">
    <style>
        body {
            min-height: 100vh;
        }
        body .container { position: relative; z-index: 1; }
        body .text-muted { color: rgba(255,255,255,0.9) !important; }
        body h2 { color: #fff; }
        .dashboard-card {
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            border: none;
        }
        .concert-list .list-group-item {
            border-radius: 14px;
            border: 1px solid rgba(15, 23, 42, 0.08);
        }
        .concert-list .badge {
            font-weight: 600;
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
        /* Hard override for readable dashboard concert rows */
        .concert-list .list-group-item,
        .concert-list .list-group-item .fw-semibold,
        .concert-list .list-group-item .small,
        .concert-list .list-group-item .text-muted,
        .concert-list .list-group-item div,
        .concert-list .list-group-item span {
            color: #000 !important;
        }
        .concert-list .list-group-item {
            background-color: #ffffff !important;
        }
        .concert-list .list-group-item .btn,
        .concert-list .list-group-item .badge {
            color: inherit;
        }
        .dashboard-header-title,
        .dashboard-header-subtitle {
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
        <div class="mb-4">
            <h2 class="mb-0 dashboard-header-title" style="color: #ffffff !important;">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
            <small class="dashboard-header-subtitle" style="color: #ffffff !important;">Booking, tickets &amp; ratings</small>
        </div>

        <div class="row g-3">
            <!-- Recommended concerts -->
            <div class="col-12 col-xl-6">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <div>
                                <h5 class="card-title mb-0">Recommended concerts</h5>
                                <small class="text-muted">Quick picks for you. Tap to book.</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="my_tickets.php" class="btn btn-outline-secondary btn-sm">My tickets</a>
                            </div>
                        </div>

                        <div class="list-group list-group-flush concert-list">
                            <?php if (empty($recommendedConcerts)): ?>
                                <div class="list-group-item px-3 py-4 text-muted text-center">No recommended concerts at the moment. Add events in Admin → Events and mark them as featured.</div>
                            <?php else: ?>
                            <?php foreach ($recommendedConcerts as $c): ?>
                                <?php
                                $qs = http_build_query([
                                    'event_id' => $c['id'] ?? '',
                                    'event_name' => $c['name'],
                                    'event_date' => $c['date'],
                                    'event_location' => $c['location'],
                                    'regular_price' => $c['regular_price'],
                                    'vip_price' => $c['vip_price'],
                                ]);
                                ?>
                                <div class="list-group-item px-3 py-3">
                                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                                        <div class="d-flex align-items-start gap-2">
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($c['name']); ?></div>
                                                <div class="text-muted small">
                                                    Date: <?php echo htmlspecialchars($c['date']); ?>
                                                    &nbsp;•&nbsp; Location: <?php echo htmlspecialchars($c['location']); ?>
                                                    &nbsp;•&nbsp; Price: ₱<?php echo number_format((float)$c['regular_price'], 2); ?>
                                                </div>
                                            </div>
                                            <span class="badge text-bg-secondary"><?php echo htmlspecialchars($c['tag']); ?></span>
                                        </div>
                                        <a class="btn btn-success btn-sm" href="ticket_booking.php?<?php echo htmlspecialchars($qs); ?>">
                                            Book
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming concerts -->
            <div class="col-12 col-xl-6">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <div>
                                <h5 class="card-title mb-0">Upcoming concerts</h5>
                                <small class="text-muted">Plan ahead. Reserve early.</small>
                            </div>
                        </div>

                        <div class="list-group list-group-flush concert-list">
                            <?php if (empty($upcomingConcerts)): ?>
                                <div class="list-group-item px-3 py-4 text-muted text-center">No upcoming concerts. Add events in Admin → Events.</div>
                            <?php else: ?>
                            <?php foreach ($upcomingConcerts as $c): ?>
                                <?php
                                $qs = http_build_query([
                                    'event_id' => $c['id'] ?? '',
                                    'event_name' => $c['name'],
                                    'event_date' => $c['date'],
                                    'event_location' => $c['location'],
                                    'price' => $c['price'],
                                ]);
                                ?>
                                <div class="list-group-item px-3 py-3">
                                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                                        <div class="d-flex align-items-start gap-2">
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($c['name']); ?></div>
                                                <div class="text-muted small">
                                                    Date: <?php echo htmlspecialchars($c['date']); ?>
                                                    &nbsp;•&nbsp; Location: <?php echo htmlspecialchars($c['location']); ?>
                                                    &nbsp;•&nbsp; Price: ₱<?php echo number_format((float)$c['regular_price'], 2); ?>
                                                </div>
                                            </div>
                                            <span class="badge text-bg-secondary"><?php echo htmlspecialchars($c['tag']); ?></span>
                                        </div>
                                        <a class="btn btn-success btn-sm" href="ticket_booking.php?<?php echo htmlspecialchars($qs); ?>">
                                            Book
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
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