<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$dbname = "bookingphp"; // Must match image_b6f39c.png exactly

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lightweight auto-migration for new ticket fields (safe to run repeatedly)
    $checkColStmt = $conn->prepare("
        SELECT COUNT(*) AS c
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = ?
          AND TABLE_NAME = 'tickets'
          AND COLUMN_NAME = ?
    ");

    $checkColStmt->execute([$dbname, 'event_location']);
    $hasLocation = (int)$checkColStmt->fetchColumn() > 0;
    if (!$hasLocation) {
        $conn->exec("ALTER TABLE tickets ADD COLUMN event_location VARCHAR(120) NULL AFTER event_date");
    }

    $checkColStmt->execute([$dbname, 'price']);
    $hasPrice = (int)$checkColStmt->fetchColumn() > 0;
    if (!$hasPrice) {
        $conn->exec("ALTER TABLE tickets ADD COLUMN price DECIMAL(10,2) NULL AFTER quantity");
    }

    $checkColStmt->execute([$dbname, 'chair_number']);
    $hasChairNumber = (int)$checkColStmt->fetchColumn() > 0;
    if (!$hasChairNumber) {
        $conn->exec("ALTER TABLE tickets ADD COLUMN chair_number VARCHAR(50) DEFAULT NULL AFTER ticket_code");
    }

    $checkColStmt->execute([$dbname, 'seat_type']);
    $hasSeatType = (int)$checkColStmt->fetchColumn() > 0;
    if (!$hasSeatType) {
        $conn->exec("ALTER TABLE tickets ADD COLUMN seat_type ENUM('Regular','VIP') DEFAULT 'Regular' AFTER chair_number");
    }

    $checkColStmt->execute([$dbname, 'payment_method']);
    $hasPaymentMethod = (int)$checkColStmt->fetchColumn() > 0;
    if (!$hasPaymentMethod) {
        $conn->exec("ALTER TABLE tickets ADD COLUMN payment_method ENUM('Cash','Credit Card','Gcash','Paymaya') DEFAULT 'Cash' AFTER seat_type");
    }

    // Ensure events table exists (for admin-managed concert events)
    $hasEventsTable = $conn->query("
        SELECT COUNT(*) FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = " . $conn->quote($dbname) . " AND TABLE_NAME = 'events'
    ")->fetchColumn() > 0;
    if (!$hasEventsTable) {
        $conn->exec("
            CREATE TABLE IF NOT EXISTS events (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                event_date DATE NOT NULL,
                location VARCHAR(150) NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                regular_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                vip_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                is_featured TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    } else {
        // Check and add regular_price and vip_price columns to events table if they don't exist
        $checkEventsColStmt = $conn->prepare("
            SELECT COUNT(*) AS c
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'events'
              AND COLUMN_NAME = ?
        ");
        $checkEventsColStmt->execute([$dbname, 'regular_price']);
        $hasRegularPrice = (int)$checkEventsColStmt->fetchColumn() > 0;
        if (!$hasRegularPrice) {
            $conn->exec("ALTER TABLE events ADD COLUMN regular_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER price");
        }
        $checkEventsColStmt->execute([$dbname, 'vip_price']);
        $hasVIPPrice = (int)$checkEventsColStmt->fetchColumn() > 0;
        if (!$hasVIPPrice) {
            $conn->exec("ALTER TABLE events ADD COLUMN vip_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER regular_price");
        }
    }
} catch(PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>