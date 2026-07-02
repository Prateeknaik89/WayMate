<?php
session_start();
require_once '../config/db.php';

// 1. Security Check: Kick them out if they aren't logged in, aren't a driver, or didn't POST data
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../driver/dashboard.php");
    exit();
}



$driver_id = $_SESSION['user_id'];

// 2. Grab the inputs from the shiny new Geoapify form
$source_raw = $_POST['source_name'] ?? '';
$dest_raw = $_POST['destination_name'] ?? '';
$date = $_POST['ride_date'] ?? '';
$time = $_POST['ride_time'] ?? '';
$seats = $_POST['available_seats'] ?? '';
$price = $_POST['price'] ?? '';

// 🚨 NEW: 2.5 Grab the exact GPS coordinates we hid in the form in Step 2!
$driver_lat1 = $_POST['source_lat'] ?? ''; 
$driver_lon1 = $_POST['source_lon'] ?? '';
$driver_lat2 = $_POST['dest_lat'] ?? '';
$driver_lon2 = $_POST['dest_lon'] ?? '';

// 3. Clean the Strings! 
$source_clean = trim(explode(',', $source_raw)[0]);
$dest_clean = trim(explode(',', $dest_raw)[0]);

// Prevent empty submissions (Added Lat/Lon to the check to be safe)
if (empty($source_clean) || empty($dest_clean) || empty($date) || empty($time) || empty($driver_lat1) || empty($driver_lat2)) {
    header("Location: ../driver/offer_ride.php?error=empty_fields");
    exit();
}

try {
    // Start a Database Transaction
    $pdo->beginTransaction();

    // --- HELPER FUNCTION: Get existing location ID, or create a new one! ---
    function getOrCreateLocationId($pdo, $location_name) {
        $stmt = $pdo->prepare("SELECT location_id FROM locations WHERE location_name LIKE ? LIMIT 1");
        $stmt->execute(["%$location_name%"]);
        $row = $stmt->fetch();
        
        if ($row) {
            return $row['location_id'];
        } else {
            $insert_stmt = $pdo->prepare("INSERT INTO locations (location_name) VALUES (?)");
            $insert_stmt->execute([$location_name]);
            return $pdo->lastInsertId();
        }
    }

    // 4. Magically get the IDs for our source and destination
    $source_id = getOrCreateLocationId($pdo, $source_clean);
    $dest_id = getOrCreateLocationId($pdo, $dest_clean);

    // 🚨 NEW: 4.5 Ask Geoapify for the driving route polyline!
    // IMPORTANT: Put your actual Geoapify API key here!
    $api_key = "db1860f6b2bd44a29e66ce1c8bf445fd"; 
    $routing_url = "https://api.geoapify.com/v1/routing?waypoints={$driver_lat1},{$driver_lon1}|{$driver_lat2},{$driver_lon2}&mode=drive&apiKey={$api_key}";

    // Fetch the data from Geoapify
    $response = file_get_contents($routing_url);
    
    $route_data = json_decode($response, true);

    // Convert the Geoapify array into a MySQL LINESTRING
    $linestring_str = null; 
    if (isset($route_data['features'][0]['geometry']['coordinates'][0])) {
        $coordinates = $route_data['features'][0]['geometry']['coordinates'][0];
        $linestring_points = [];
        
        foreach ($coordinates as $point) {
            $lon = $point[0];
            $lat = $point[1];
            $linestring_points[] = $lon . ' ' . $lat; 
        }
        $linestring_str = "LINESTRING(" . implode(", ", $linestring_points) . ")";
    } else {
        // If Geoapify fails to find a route, rollback and show an error
        $pdo->rollBack();
        header("Location: ../driver/offer_ride.php?error=routing_failed");
        exit();
    }

    // 🚨 NEW: 5. Insert the new ride, including route_path and ST_GeomFromText()
    $sql = "INSERT INTO rides (driver_id, source_id, destination_id, route_path, ride_date, ride_time, available_seats, price_per_seat, status) VALUES (?, ?, ?, ST_GeomFromText(?), ?, ?, ?, ?, 'active')";
    
    $stmt_ride = $pdo->prepare($sql);
    $stmt_ride->execute([$driver_id, $source_id, $dest_id, $linestring_str, $date, $time, $seats, $price]);

    // 6. Save changes and celebrate
    $pdo->commit();
    header("Location: ../driver/dashboard.php?success=trip_posted");
    exit();

} catch (PDOException $e) {
    // If anything fails, cancel the database updates
    $pdo->rollBack();
    header("Location: ../driver/offer_ride.php?error=db_error");
    exit();
}
?>