<?php
session_start();
require_once '../config/db.php';

// 1. Kick back if they aren't logged in or if they just typed the URL in the browser
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

$driver_id = $_SESSION['user_id'];
$booking_id = $_POST['booking_id'] ?? null;
$action = $_POST['action'] ?? null; // Will be either 'accept' or 'reject'

// 2. Make sure they didn't tamper with the form data
if (!$booking_id || !in_array($action, ['accept', 'reject'])) {
    header("Location: dashboard.php?error=invalid_request");
    exit();
}

try {
    // Start a Database Transaction to keep our tables perfectly synced
    $pdo->beginTransaction();

    // 3. Verify this booking actually belongs to a ride THIS driver owns
    $verify_stmt = $pdo->prepare("
        SELECT b.ride_id, r.available_seats 
        FROM bookings b 
        JOIN rides r ON b.ride_id = r.ride_id 
        WHERE b.booking_id = ? AND r.driver_id = ? AND b.status = 'pending'
    ");
    $verify_stmt->execute([$booking_id, $driver_id]);
    $booking_data = $verify_stmt->fetch();

    // If we didn't find the booking (or it's already processed), stop here.
    if (!$booking_data) {
        $pdo->rollBack();
        header("Location: dashboard.php?error=not_found_or_processed");
        exit();
    }

    $ride_id = $booking_data['ride_id'];
    $available_seats = $booking_data['available_seats'];

    // 4. Handle the ACCEPT action
    if ($action === 'accept') {
        
        // Prevent overbooking!
        if ($available_seats <= 0) {
            $pdo->rollBack();
            header("Location: dashboard.php?error=ride_full");
            exit();
        }

        // Update booking status to confirmed
        $stmt1 = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE booking_id = ?");
        $stmt1->execute([$booking_id]);

        // Reduce the number of available seats in the car by 1
        $stmt2 = $pdo->prepare("UPDATE rides SET available_seats = available_seats - 1 WHERE ride_id = ?");
        $stmt2->execute([$ride_id]);

    // 5. Handle the REJECT action
    } elseif ($action === 'reject') {
        
        // We only need to update the booking status. The seats stay the same.
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'rejected' WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        
    }

    // 6. Save the changes and kick them back to the dashboard!
    $pdo->commit();
    header("Location: dashboard.php?success=" . $action);
    exit();

} catch (Exception $e) {
    // If anything goes wrong, cancel the changes
    $pdo->rollBack();
    header("Location: dashboard.php?error=db_error");
    exit();
}
?>