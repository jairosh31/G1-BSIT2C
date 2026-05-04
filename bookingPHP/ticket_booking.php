<?php
// Ticket booking page (creates tickets for logged-in user)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";
$prefillEventName = trim($_GET['event_name'] ?? "");
$prefillEventDate = trim($_GET['event_date'] ?? "");
$prefillEventLocation = trim($_GET['event_location'] ?? "");
$prefillRegularPrice = trim($_GET['regular_price'] ?? "");
$prefillVIPPrice = trim($_GET['vip_price'] ?? "");
$prefillRegularPriceFloat = is_numeric($prefillRegularPrice) ? (float)$prefillRegularPrice : 0.0;
$prefillVIPPriceFloat = is_numeric($prefillVIPPrice) ? (float)$prefillVIPPrice : 0.0;
$formEventName = $prefillEventName;
$formEventDate = $prefillEventDate;
$formEventLocation = $prefillEventLocation;
$formRegularPrice = $prefillRegularPriceFloat > 0 ? $prefillRegularPriceFloat : 0.0;
$formVIPPrice = $prefillVIPPriceFloat > 0 ? $prefillVIPPriceFloat : 0.0;
$formQuantity = 1;
$formChairNumber = '';
$formSeatType = 'Regular';
$formPaymentMethod = 'Cash';

// Get booked chairs for current event (if event details are available)
$bookedChairs = [];
if (!empty($prefillEventName) && !empty($prefillEventDate)) {
    try {
        $stmt = $conn->prepare("SELECT chair_number FROM tickets WHERE event_name = ? AND event_date = ? AND status = 'active'");
        $stmt->execute([$prefillEventName, $prefillEventDate]);
        $bookedChairs = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    } catch (PDOException $e) {
        // Ignore errors for booked chairs
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $eventName = trim($_POST["event_name"] ?? "");
    $eventDate = trim($_POST["event_date"] ?? "");
    $eventLocation = trim($_POST["event_location"] ?? "");
    $quantity  = (int)($_POST["quantity"] ?? 0);
    $regularPrice = (float)($_POST["regular_price"] ?? 0);
    $vipPrice = (float)($_POST["vip_price"] ?? 0);
    $chairNumber = trim($_POST["chair_number"] ?? "");
    $seatType = trim($_POST["seat_type"] ?? "Regular");
    $paymentMethod = trim($_POST["payment_method"] ?? "Cash");
    $userId    = (int)$_SESSION["user_id"];

    $formEventName = $eventName;
    $formEventDate = $eventDate;
    $formEventLocation = $eventLocation;
    $formRegularPrice = $regularPrice;
    $formVIPPrice = $vipPrice;
    $formQuantity = max(1, $quantity);
    $formChairNumber = $chairNumber;
    $formSeatType = $seatType;
    $formPaymentMethod = $paymentMethod;
    // Determine price based on seat type
    $price = ($seatType === 'VIP') ? $vipPrice : $regularPrice;

    if ($eventName === "" || $eventDate === "" || $eventLocation === "" || $quantity < 1 || $regularPrice <= 0 || $vipPrice <= 0 || empty($chairNumber)) {
        $message = "<div class='alert alert-danger'>Please fill in all fields and select a chair.</div>";
    } else {
        // Check if chair is already booked for this event
        try {
            $checkStmt = $conn->prepare("SELECT id FROM tickets WHERE event_name = ? AND event_date = ? AND chair_number = ? AND status = 'active'");
            $checkStmt->execute([$eventName, $eventDate, $chairNumber]);
            if ($checkStmt->rowCount() > 0) {
                $message = "<div class='alert alert-danger'>Sorry, Chair $chairNumber is already booked for this event!</div>";
            } else {
                // Simple ticket code (can be shown to customer)
                $ticketCode = "TKT-" . strtoupper(substr(md5($userId . microtime(true)), 0, 8));

                try {
                    $stmt = $conn->prepare("
                        INSERT INTO tickets (user_id, event_name, event_date, event_location, quantity, price, ticket_code, chair_number, seat_type, payment_method)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $eventName, $eventDate, $eventLocation, $quantity, $price, $ticketCode, $chairNumber, $seatType, $paymentMethod]);

                    $message = "<div class='alert alert-success'>
                        Booking confirmed! Your ticket code is <strong>" . htmlspecialchars($ticketCode) . "</strong>.
                        You can view all your tickets on the <a href='my_tickets.php' class='alert-link'>My tickets</a> page.
                    </div>";
                } catch (PDOException $e) {
                    $message = "<div class='alert alert-danger'>Error creating booking: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Error checking chair availability: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Tickets</title>
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
        .booking-card {
            max-width: 1100px; /* Further increased for full horizontal layout */
            width: 100%;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.18);
            padding: 28px 30px 26px;
        }
        /* Theater Layout Styles */
        .theater-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: rgba(13, 24, 43, 0.03);
            border-radius: 20px;
            margin-top: 20px;
            width: 100%;
            overflow-x: auto;
        }
        .stage-box {
            width: 50%;
            height: 50px;
            background: #2c3e50;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            letter-spacing: 5px;
            border-radius: 0 0 50px 50px;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .seating-sections {
            display: flex;
            justify-content: center;
            gap: 30px;
            width: 100%;
            min-width: 900px; /* Ensure it doesn't stack vertically */
        }
        .seating-section {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .section-name {
            font-size: 0.65rem;
            text-align: center;
            color: #666;
            font-weight: bold;
            text-transform: uppercase;
        }
        .seat-row {
            display: flex;
            align-items: center;
            gap: 5px;
            justify-content: center;
        }
        .row-label {
            width: 15px;
            font-size: 0.7rem;
            font-weight: bold;
            color: #999;
        }
        .seats-container {
            display: flex;
            gap: 4px;
            flex-direction: row !important; /* Force horizontal seats */
        }
        .seat-box {
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.55rem;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid rgba(0,0,0,0.1);
        }
        /* Area Colors */
        .seat-vip { background-color: #ffeb3b !important; color: #000 !important; border-color: #fdd835 !important; }
        .seat-regular { background-color: #4caf50 !important; color: #fff !important; border-color: #388e3c !important; }
        
        .seat-box.booked {
            background-color: #f44336 !important;
            color: #fff !important;
            cursor: not-allowed;
            border-color: #d32f2f !important;
            opacity: 1 !important;
        }
        .seat-box.selected {
            background-color: #2196f3 !important;
            color: #fff !important;
            transform: scale(1.2);
            z-index: 2;
            box-shadow: 0 0 15px rgba(33, 150, 243, 0.7);
            border: 2px solid #fff !important;
        }
        .lower-aisles {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top: 10px;
        }
        /* Legend Styles */
        .legend-item { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; color: #444; }
        .legend-box { width: 20px; height: 20px; border-radius: 4px; border: 1px solid rgba(0,0,0,0.1); }
        .legend-box.booked { background-color: #f44336 !important; border-color: #d32f2f !important; }
        .legend-box.selected { background-color: #2196f3 !important; border-color: #1976d2 !important; }
        /* Hard override for readable form text */
        .booking-card,
        .booking-card h1,
        .booking-card h2,
        .booking-card h3,
        .booking-card h4,
        .booking-card h5,
        .booking-card p,
        .booking-card small,
        .booking-card label,
        .booking-card span,
        .booking-card div {
            color: #000 !important;
        }
        .booking-card .form-control,
        .booking-card .form-select,
        .booking-card input,
        .booking-card select,
        .booking-card textarea {
            background: #fff !important;
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
        }
        .booking-card .form-control::placeholder {
            color: #6b7280 !important;
        }
        .booking-card .form-label,
        .booking-card .mb-3,
        .booking-card .mb-4 {
            color: #000 !important;
        }
        .booking-card select option {
            color: #000 !important;
            background-color: #fff !important;
        }
        .booking-card .theater-container,
        .booking-card .theater-container .section-name,
        .booking-card .theater-container .row-label,
        .booking-card .theater-container .legend-item {
            color: #000 !important;
        }
        .booking-card .stage-box {
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
            <div class="d-flex justify-content-center">
                <div class="booking-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-0">Book your ticket</h4>
                            <small class="text-muted">Fill in the details below.</small>
                        </div>
                        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Back</a>
                    </div>

    <?php echo $message; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Concert Name</label>
            <input type="text" name="event_name" class="form-control" placeholder="Concert"
                   value="<?php echo htmlspecialchars($formEventName); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="event_date" class="form-control"
                   value="<?php echo htmlspecialchars($formEventDate); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="event_location" class="form-control" placeholder="Event location"
                   value="<?php echo htmlspecialchars($formEventLocation); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Regular Seat Price</label>
            <input type="number" name="regular_price" min="0" step="0.01" class="form-control" placeholder="0.00"
                   value="<?php echo htmlspecialchars((string)$formRegularPrice); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">VIP Seat Price</label>
            <input type="number" name="vip_price" min="0" step="0.01" class="form-control" placeholder="0.00"
                   value="<?php echo htmlspecialchars((string)$formVIPPrice); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Number of tickets</label>
            <input type="number" name="quantity" min="1" value="<?php echo (int)$formQuantity; ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Seat Type</label>
            <select name="seat_type" id="seat_type" class="form-select" style="color:#000;background:#fff;" required>
                <option style="color:#000;background:#fff;" value="Regular" <?php echo $formSeatType === 'Regular' ? 'selected' : ''; ?>>Regular</option>
                <option style="color:#000;background:#fff;" value="VIP" <?php echo $formSeatType === 'VIP' ? 'selected' : ''; ?>>VIP</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label d-block text-center fw-bold">Select Your Seat</label>
            <input type="hidden" name="chair_number" id="selected_chair" value="<?php echo htmlspecialchars($formChairNumber); ?>" required>
            
            <div class="d-flex justify-content-center gap-3 mb-3">
                <div class="legend-item"><div class="legend-box seat-vip"></div> VIP</div>
                <div class="legend-item"><div class="legend-box seat-regular"></div> Regular</div>
                <div class="legend-item"><div class="legend-box booked"></div> Booked</div>
                <div class="legend-item"><div class="legend-box selected"></div> Selected</div>
            </div>

            <div class="theater-container">
                <?php
                $bookedChairs = [];
                if (!empty($prefillEventName) && !empty($prefillEventDate)) {
                    try {
                        $stmt = $conn->prepare("SELECT chair_number FROM tickets WHERE event_name = ? AND event_date = ? AND status = 'active'");
                        $stmt->execute([$prefillEventName, $prefillEventDate]);
                        $bookedChairs = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
                    } catch (PDOException $e) {}
                }

                $rows = ['K', 'J', 'I', 'H', 'G', 'F', 'E', 'D', 'C', 'B', 'A'];
                $rowsFrontToBack = array_reverse($rows);
                $sections = [
                    'Left'   => 5,
                    'Center' => 15,
                    'Right'  => 5
                ];

                // Pre-calculate sequential numbering starting from Front Row (A) 
                // and moving Left to Right across sections.
                $seatMapping = [];
                $globalCounter = 1;
                foreach ($rowsFrontToBack as $rowName) {
                    foreach ($sections as $secName => $seatsCount) {
                        for ($s = 1; $s <= $seatsCount; $s++) {
                            $seatMapping[$secName][$rowName][$s] = $globalCounter++;
                        }
                    }
                }
                ?>

                <div class="seating-sections">
                    <?php foreach ($sections as $sectionName => $seatsPerRow): ?>
                        <div class="seating-section">
                            <div class="section-name"><?php echo $sectionName; ?></div>
                            <?php foreach ($rows as $row): ?>
                                <div class="seat-row">
                                    <div class="row-label"><?php echo $row; ?></div>
                                    <div class="seats-container">
                                        <?php for ($s = 1; $s <= $seatsPerRow; $s++): 
                                            $currentSeatNum = $seatMapping[$sectionName][$row][$s];
                                            
                                            // Regular in back (K, J, I, H, G), VIP in front (F, E, D, C, B, A)
                                            $isRegular = in_array($row, ['K', 'J', 'I', 'H', 'G']);
                                            $areaName = $isRegular ? 'Regular' : 'VIP';
                                            
                                            $seatValue = "Chair $currentSeatNum ($areaName - $sectionName Row $row)";
                                            $isBooked = in_array($seatValue, $bookedChairs);
                                            $isSelected = ($formChairNumber === $seatValue);
                                        ?>
                                            <div class="seat-box <?php echo $isRegular ? 'seat-regular' : 'seat-vip'; ?> <?php echo $isBooked ? 'booked' : ''; ?> <?php echo $isSelected ? 'selected' : ''; ?>" 
                                                 data-seat="<?php echo htmlspecialchars($seatValue); ?>"
                                                 data-type="<?php echo $areaName; ?>"
                                                 title="<?php echo $seatValue; ?>">
                                                <?php echo $currentSeatNum; ?>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="row-label"><?php echo $row; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="stage-box">STAGE</div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label d-block">Payment Method</label>
            <div class="d-flex flex-wrap gap-2">
                <input type="radio" class="btn-check" name="payment_method" id="pm_cash" value="Cash" <?php echo $formPaymentMethod === 'Cash' ? 'checked' : ''; ?> required>
                <label class="btn btn-outline-primary btn-sm" for="pm_cash">Cash</label>

                <input type="radio" class="btn-check" name="payment_method" id="pm_credit" value="Credit Card" <?php echo $formPaymentMethod === 'Credit Card' ? 'checked' : ''; ?> required>
                <label class="btn btn-outline-primary btn-sm" for="pm_credit">Credit Card</label>

                <input type="radio" class="btn-check" name="payment_method" id="pm_gcash" value="Gcash" <?php echo $formPaymentMethod === 'Gcash' ? 'checked' : ''; ?> required>
                <label class="btn btn-outline-primary btn-sm" for="pm_gcash">Gcash</label>

                <input type="radio" class="btn-check" name="payment_method" id="pm_paymaya" value="Paymaya" <?php echo $formPaymentMethod === 'Paymaya' ? 'checked' : ''; ?> required>
                <label class="btn btn-outline-primary btn-sm" for="pm_paymaya">Paymaya</label>
            </div>
        </div>

                    <button type="submit" class="btn btn-primary w-100">Confirm booking</button>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Seat Selection Logic
document.querySelectorAll('.seat-box:not(.booked)').forEach(seat => {
    seat.addEventListener('click', function() {
        // Remove selection from others
        document.querySelectorAll('.seat-box').forEach(s => s.classList.remove('selected'));
        // Add to clicked
        this.classList.add('selected');
        // Update hidden input
        document.getElementById('selected_chair').value = this.getAttribute('data-seat');
        
        // Auto-select the corresponding Seat Type dropdown
        const type = this.getAttribute('data-type');
        document.getElementById('seat_type').value = type;
        
        // Update price summary
        updateSummary();
    });
});
</script>
</body>
</html>

