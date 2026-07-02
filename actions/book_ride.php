<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['ride_id'])) {
    header("Location: ../index.php");
    exit();
}

$ride_id = $_GET['ride_id'];
$passenger_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // 1. Check if seats are still available
    $stmt = $pdo->prepare("SELECT available_seats, driver_id FROM rides WHERE ride_id = ? FOR UPDATE");
    $stmt->execute([$ride_id]);
    $ride = $stmt->fetch();

    if ($ride && $ride['available_seats'] > 0) {
        // Prevent driver from booking their own ride
        if ($ride['driver_id'] == $passenger_id) {
            header("Location: ../passenger/search_results.php?error=own_ride");
            exit();
        }

        // 2. Insert the booking
        // Inside book_ride.php
        $book = $pdo->prepare("INSERT INTO bookings (ride_id, passenger_id) VALUES (?, ?)");
        $book->execute([$ride_id, $passenger_id]);

        // 3. Update available seats
        $update = $pdo->prepare("UPDATE rides SET available_seats = available_seats - 1 WHERE ride_id = ?");
        $update->execute([$ride_id]);

        $pdo->commit();

// MAKE SURE THIS REDIRECT IS CORRECT
header("Location: ../passenger/booking_success.php?ride_id=" . $ride_id);
exit();
    } else {
        $pdo->rollBack();
        header("Location: ../passenger/search_results.php?error=no_seats");
    }
} catch (Exception $e) {
    $pdo->rollBack();
    // This will tell you exactly what the database is complaining about
    echo "DEBUG ERROR: " . $e->getMessage(); 
    exit();
}