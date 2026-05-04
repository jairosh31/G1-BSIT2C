<?php
// Cancel a ticket (booking) for the logged-in user
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ticket_id"])) {
    $ticketId = (int)$_POST["ticket_id"];
    $userId   = (int)$_SESSION["user_id"];
    $isAdmin  = ($_SESSION['role'] ?? '') === 'admin';

    try {
        if ($isAdmin) {
            // Admin can cancel any ticket
            $stmt = $conn->prepare("
                UPDATE tickets
                SET status = 'cancelled'
                WHERE id = ?
            ");
            $stmt->execute([$ticketId]);
        } else {
            // Regular user can only cancel their own tickets
            $stmt = $conn->prepare("
                UPDATE tickets
                SET status = 'cancelled'
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$ticketId, $userId]);
        }
    } catch (PDOException $e) {
        // You could log this error in a real app
    }
}

// Redirect back to appropriate page
$isAdmin  = ($_SESSION['role'] ?? '') === 'admin';
if ($isAdmin) {
    header("Location: admin_tickets.php");
} else {
    header("Location: my_tickets.php");
}
exit;


