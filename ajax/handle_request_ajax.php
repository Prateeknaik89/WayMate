<?php
session_start();
// Use the absolute path to your DB to prevent errors
require_once __DIR__ . '/../config/db.php'; 

header('Content-Type: application/json');

if (isset($_GET['id']) && isset($_GET['action'])) {
    $booking_id = $_GET['id'];
    $action = $_GET['action'];
    $new_status = ($action === 'accept') ? 'confirmed' : 'rejected';

    try {
        $pdo->beginTransaction();

        // 1. Update Booking Status
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $stmt->execute([$new_status, $booking_id]);

        // 2. If Accepted, find the Ride ID and subtract 1 seat
        if ($action === 'accept') {
            // Get the ride_id linked to this booking
            $get_ride = $pdo->prepare("SELECT ride_id FROM bookings WHERE booking_id = ?");
            $get_ride->execute([$booking_id]);
            $ride_id = $get_ride->fetchColumn();

            // Update the seats in the rides table
            $update_seats = $pdo->prepare("UPDATE rides SET available_seats = available_seats - 1 WHERE ride_id = ? AND available_seats > 0");
            $update_seats->execute([$ride_id]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
}