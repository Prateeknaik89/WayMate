<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Unauthorized');
}

$ride_id = $_POST['ride_id'];
$driver_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // 1. Mark the entire ride as cancelled (Safety check: ensure they are the actual driver)
    $stmt = $pdo->prepare("UPDATE rides SET status = 'cancelled' WHERE ride_id = ? AND driver_id = ?");
    $stmt->execute([$ride_id, $driver_id]);

    // 2. Cancel ALL bookings associated with this ride
    $cancel_bookings = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE ride_id = ?");
    $cancel_bookings->execute([$ride_id]);

    $pdo->commit();
    header("Location: ../driver/dashboard.php?msg=trip_cancelled");

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error cancelling trip.");
}
?>