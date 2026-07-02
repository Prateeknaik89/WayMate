<?php
session_start();
require_once '../config/db.php';

if (isset($_GET['id']) && isset($_GET['action'])) {
    $booking_id = $_GET['id'];
    $action = $_GET['action'];
    $new_status = ($action === 'accept') ? 'confirmed' : 'rejected';

    try {
        $pdo->beginTransaction();

        // 1. Update the booking status
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $stmt->execute([$new_status, $booking_id]);

        // 2. If accepted, subtract a seat from the ride
        if ($action === 'accept') {
            $stmt_seat = $pdo->prepare("UPDATE rides r 
                                       JOIN bookings b ON r.ride_id = b.ride_id 
                                       SET r.available_seats = r.available_seats - 1 
                                       WHERE b.booking_id = ?");
            $stmt_seat->execute([$booking_id]);
        }

        $pdo->commit();
        header("Location: ../driver/dashboard.php?msg=updated");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}