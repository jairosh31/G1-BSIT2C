<?php
// Booking / Rating page (5-star rating)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rating  = isset($_POST["rating"]) ? (int) $_POST["rating"] : 0;
    $comment = isset($_POST["comment"]) ? trim($_POST["comment"]) : "";
    $userId  = (int) $_SESSION["user_id"];

    if ($rating < 1 || $rating > 5) {
        $message = "<div class='alert alert-danger'>Please select a rating between 1 and 5 stars.</div>";
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO ratings (user_id, source, rating, comment)
                VALUES (?, 'php', ?, ?)
            ");
            $stmt->execute([$userId, $rating, $comment]);
            $message = "<div class='alert alert-success'>Thank you! Your rating has been saved.</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Error saving rating: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Fetch average rating and total ratings
$avgRating = null;
$totalRatings = 0;
try {
    $stmt = $conn->query("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM ratings");
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $avgRating = $row["avg_rating"];
        $totalRatings = (int) $row["total"];
    }
} catch (PDOException $e) {
    // Silent fail for stats
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rate Our Booking Service</title>
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
        .rating-card {
            max-width: 500px;
            width: 100%;
            border-radius: 20px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.9) !important; /* Solid white-ish background */
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        /* Force everything inside the card to be DARK for visibility on white */
        .rating-card, 
        .rating-card h3, 
        .rating-card p, 
        .rating-card label,
        .rating-card .avg-rating,
        .rating-card .form-label,
        .rating-card .text-muted,
        .rating-card strong,
        .rating-card span {
            color: #212529 !important;
            text-shadow: none !important;
        }
        .rating-card .btn-link {
            color: #0d6efd !important;
            text-decoration: underline;
            font-weight: bold;
        }
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 8px;
            margin-bottom: 15px;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            font-size: 36px;
            cursor: pointer;
            transition: color 0.2s;
            color: #ccc !important; /* Light gray for unselected on white background */
        }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffc107 !important;
        }
        .star-rating input:checked + label {
            transform: scale(1.05);
        }
        .avg-rating {
            font-size: 15px;
        }
        /* Hard override for readable rating form text */
        .rating-card,
        .rating-card *:not(.btn):not(.star-rating label) {
            color: #000 !important;
            text-shadow: none !important;
        }
        .rating-card .form-control,
        .rating-card textarea,
        .rating-card input {
            background: #fff !important;
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
        }
        .rating-card .form-control::placeholder {
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
            <div class="d-flex justify-content-center">
                <div class="rating-card">
                    <h3 class="mb-2 text-center">Rate Your Booking Experience</h3>
                    <p class="text-muted text-center mb-3">Tap a star to rate from 1 to 5.</p>

    <?php echo $message; ?>

    <?php if ($avgRating !== null): ?>
        <p class="avg-rating text-center mb-3">
            Average rating:
            <strong><?php echo number_format($avgRating, 1); ?> / 5</strong>
            (<?php echo $totalRatings; ?> review<?php echo $totalRatings === 1 ? "" : "s"; ?>)
        </p>
    <?php endif; ?>

    <form method="post">
        <div class="star-rating mb-3">
            <input type="radio" id="star5" name="rating" value="5">
            <label for="star5">&#9733;</label>

            <input type="radio" id="star4" name="rating" value="4">
            <label for="star4">&#9733;</label>

            <input type="radio" id="star3" name="rating" value="3">
            <label for="star3">&#9733;</label>

            <input type="radio" id="star2" name="rating" value="2">
            <label for="star2">&#9733;</label>

            <input type="radio" id="star1" name="rating" value="1">
            <label for="star1">&#9733;</label>
        </div>

        <div class="mb-3">
            <label class="form-label">Optional comment</label>
            <textarea name="comment" class="form-control" rows="3" placeholder="Tell us more about your experience..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">Submit Rating</button>

                    <a href="dashboard.php" class="btn btn-link w-100 mt-2">Back to Dashboard</a>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


