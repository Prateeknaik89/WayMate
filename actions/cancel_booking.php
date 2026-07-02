<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Unauthorized');
}

$booking_id = $_POST['booking_id'];
$passenger_id = $_SESSION['user_id'];

try {
    // Start Transaction
    $pdo->beginTransaction();

    // 1. Get the current status and the ride_id
    $stmt = $pdo->prepare("SELECT status, ride_id FROM bookings WHERE booking_id = ? AND passenger_id = ?");
    $stmt->execute([$booking_id, $passenger_id]);
    $booking = $stmt->fetch();

    if ($booking && $booking['status'] !== 'cancelled') {
        
        // 2. Mark the booking as cancelled
        $update_booking = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
        $update_booking->execute([$booking_id]);

        // 3. IF the ride was already confirmed, give the seat back to the driver!
        if ($booking['status'] === 'confirmed') {
            $give_seat_back = $pdo->prepare("UPDATE rides SET available_seats = available_seats + 1 WHERE ride_id = ?");
            $give_seat_back->execute([$booking['ride_id']]);
        }
    }

    // Commit the changes
    $pdo->commit();
    header("Location: ../passenger/dashboard.php?msg=cancelled");

} catch (Exception $e) {
    $pdo->rollBack(); // If anything fails, undo everything
    die("Error cancelling ride.");
}
?>